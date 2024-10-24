<?php

namespace wcf\system\label;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages labels and label-to-object associations.
 *
 * @author  Alexander Ebert, Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LabelHandler extends SingletonFactory
{
    /**
     * cached list of object types
     * @var mixed[][]
     */
    protected $cache;

    /**
     * list of label groups
     * @var mixed[][]
     */
    protected $labelGroups;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->cache = [
            'objectTypes' => [],
            'objectTypeNames' => [],
        ];

        $cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.object');
        foreach ($cache as $objectType) {
            $this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
            $this->cache['objectTypeNames'][$objectType->objectType] = $objectType->objectTypeID;
        }

        $this->labelGroups = LabelCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns the id of the label ACL option with the given name or null if
     * no such option exists.
     *
     * @param string $optionName
     * @return  int|null
     */
    public function getOptionID($optionName)
    {
        foreach ($this->labelGroups['options'] as $option) {
            if ($option->optionName == $optionName) {
                return $option->optionID;
            }
        }

        return null;
    }

    /**
     * Returns the label object type with the given name or null of no such
     * object.
     *
     * @param string $objectType
     * @return  \wcf\data\object\type\ObjectType|null
     */
    public function getObjectType($objectType)
    {
        if (isset($this->cache['objectTypeNames'][$objectType])) {
            $objectTypeID = $this->cache['objectTypeNames'][$objectType];

            return $this->cache['objectTypes'][$objectTypeID];
        }

        return null;
    }

    /**
     * Returns an array with view permissions for the labels with the given id.
     *
     * @param int[] $labelIDs
     * @param User $user
     * @return  array
     * @see     \wcf\system\label\LabelHandler::getPermissions()
     */
    public function validateCanView(array $labelIDs, ?User $user = null)
    {
        return $this->getPermissions('canViewLabel', $labelIDs, $user);
    }

    /**
     * Returns an array with use permissions for the labels with the given id.
     *
     * @param int[] $labelIDs
     * @param User $user
     * @return  array
     * @see     \wcf\system\label\LabelHandler::getPermissions()
     */
    public function validateCanUse(array $labelIDs, ?User $user = null)
    {
        return $this->getPermissions('canUseLabel', $labelIDs, $user);
    }

    /**
     * Returns an array with boolean values for each given label id.
     *
     * @param string $optionName
     * @param int[] $labelIDs
     * @param User $user
     * @return  array
     * @throws  SystemException
     */
    public function getPermissions($optionName, array $labelIDs, ?User $user = null)
    {
        if (empty($labelIDs)) {
            // nothing to validate anyway
            return [];
        }

        if (empty($this->labelGroups['groups'])) {
            // pretend given label ids aren't valid
            $data = [];
            foreach ($labelIDs as $labelID) {
                $data[$labelID] = false;
            }

            return $data;
        }

        $optionID = $this->getOptionID($optionName);
        if ($optionID === null) {
            throw new SystemException("cannot validate label ids, ACL options missing");
        }

        // validate each label
        $data = [];
        foreach ($labelIDs as $labelID) {
            $isValid = false;

            foreach ($this->labelGroups['groups'] as $group) {
                if (!$group->isValid($labelID)) {
                    continue;
                }

                if (!$group->hasPermissions() || $group->getPermission($optionID, $user)) {
                    $isValid = true;
                }
            }

            $data[$labelID] = $isValid;
        }

        return $data;
    }

    /**
     * Sets labels for given object id, pass an empty array to remove all previously
     * assigned labels.
     *
     * @param int[] $labelIDs
     * @param int $objectTypeID
     * @param int $objectID
     * @param bool $validatePermissions
     */
    public function setLabels(array $labelIDs, $objectTypeID, $objectID, $validatePermissions = true)
    {
        // get accessible label ids to prevent inaccessible ones to be removed
        $accessibleLabelIDs = $this->getAccessibleLabelIDs();

        // delete previous labels
        if (!$validatePermissions || ($validatePermissions && !empty($accessibleLabelIDs))) {
            $conditions = new PreparedStatementConditionBuilder();
            if ($validatePermissions) {
                $conditions->add("labelID IN (?)", [$accessibleLabelIDs]);
            }
            $conditions->add("objectTypeID = ?", [$objectTypeID]);
            $conditions->add("objectID = ?", [$objectID]);

            $sql = "DELETE FROM wcf1_label_object
                    " . $conditions;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
        }

        // insert new labels
        if (!empty($labelIDs)) {
            $sql = "INSERT INTO wcf1_label_object
                                (labelID, objectTypeID, objectID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($labelIDs as $labelID) {
                $statement->execute([
                    $labelID,
                    $objectTypeID,
                    $objectID,
                ]);
            }
        }
    }

    /**
     * Replaces the labels of the label groups with the given ids with the labels with the given
     * ids. Existing labels of the object from other label groups will not be changed. If no
     * label for any of the given label group is given, an existing label from this group will
     * be removed.
     *
     * @param int[] $groupIDs ids of the relevant label groups
     * @param int[] $labelIDs ids of the new labels
     * @param string $objectType label object type of the updated object
     * @param int $objectID id of the updated object
     * @since   5.2
     */
    public function replaceLabels(array $groupIDs, array $labelIDs, $objectType, $objectID)
    {
        $objectTypeID = $this->getObjectType($objectType)->objectTypeID;

        // get the ids of the labels in the relevant label groups
        $replacedLabelIDs = [];
        foreach ($groupIDs as $groupID) {
            $replacedLabelIDs = \array_merge(
                $replacedLabelIDs,
                $this->getLabelGroup($groupID)->getLabelIDs()
            );
        }

        // delete old labels first
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('labelID IN (?)', [$replacedLabelIDs]);
        $conditionBuilder->add("objectTypeID = ?", [$objectTypeID]);
        $conditionBuilder->add("objectID = ?", [$objectID]);

        $sql = "DELETE FROM wcf1_label_object
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        // assign new labels
        if (!empty($labelIDs)) {
            $sql = "INSERT INTO wcf1_label_object
                                (labelID, objectTypeID, objectID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($labelIDs as $labelID) {
                $statement->execute([
                    $labelID,
                    $objectTypeID,
                    $objectID,
                ]);
            }
        }
    }

    /**
     * Returns all assigned labels, optionally filtered to validate permissions.
     *
     * @param int $objectTypeID
     * @param int[] $objectIDs
     * @param bool $validatePermissions
     * @return  Label[][]
     */
    public function getAssignedLabels($objectTypeID, array $objectIDs, $validatePermissions = true)
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$objectTypeID]);
        $conditions->add("objectID IN (?)", [$objectIDs]);
        $sql = "SELECT  objectID, labelID
                FROM    wcf1_label_object
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $labels = $statement->fetchMap('labelID', 'objectID', false);

        // optionally filter out labels without permissions
        if ($validatePermissions) {
            $labelIDs = \array_keys($labels);
            $result = $this->validateCanView($labelIDs);

            foreach ($labelIDs as $labelID) {
                if (!$result[$labelID]) {
                    unset($labels[$labelID]);
                }
            }
        }

        // reorder the array by object id
        $data = [];
        foreach ($labels as $labelID => $objectIDs) {
            foreach ($objectIDs as $objectID) {
                if (!isset($data[$objectID])) {
                    $data[$objectID] = [];
                }

                /** @var ViewableLabelGroup $group */
                foreach ($this->labelGroups['groups'] as $group) {
                    $label = $group->getLabel($labelID);
                    if ($label !== null) {
                        $data[$objectID][$labelID] = $label;
                    }
                }
            }
        }

        // order label ids by label group
        $labelGroups = &$this->labelGroups;
        foreach ($data as &$labels) {
            \uasort($labels, static function ($a, $b) use ($labelGroups) {
                $groupA = $labelGroups['groups'][$a->groupID];
                $groupB = $labelGroups['groups'][$b->groupID];

                if ($groupA->showOrder == $groupB->showOrder) {
                    return ($groupA->groupID > $groupB->groupID) ? 1 : -1;
                }

                return ($groupA->showOrder > $groupB->showOrder) ? 1 : -1;
            });
        }
        unset($labels);

        return $data;
    }

    /**
     * Returns given label groups by id.
     *
     * @param int[] $groupIDs
     * @param bool $validatePermissions
     * @param string $permission
     * @return  ViewableLabelGroup[]
     * @throws  SystemException
     */
    public function getLabelGroups(array $groupIDs = [], $validatePermissions = true, $permission = 'canSetLabel')
    {
        $data = [];

        $optionID = null;
        if ($validatePermissions) {
            $optionID = $this->getOptionID($permission);
            if ($optionID === null) {
                throw new SystemException("cannot validate group ids, ACL options missing");
            }
        }

        if (empty($groupIDs)) {
            $groupIDs = \array_keys($this->labelGroups['groups']);
        }
        foreach ($groupIDs as $groupID) {
            // validate given group ids
            if (!isset($this->labelGroups['groups'][$groupID])) {
                throw new SystemException("unknown label group identified by group id '" . $groupID . "'");
            }

            // validate permissions
            if ($validatePermissions) {
                if (
                    $this->labelGroups['groups'][$groupID]->hasPermissions()
                    && !$this->labelGroups['groups'][$groupID]->getPermission($optionID)
                ) {
                    continue;
                }
            }

            $data[$groupID] = $this->labelGroups['groups'][$groupID];
        }

        \uasort($data, [LabelGroup::class, 'sortLabelGroups']);

        return $data;
    }

    /**
     * Returns a list of accessible label ids.
     *
     * @return  int[]
     */
    public function getAccessibleLabelIDs()
    {
        $labelIDs = [];
        $groups = $this->getLabelGroups();

        foreach ($groups as $group) {
            $labelIDs = \array_merge($labelIDs, $group->getLabelIDs());
        }

        return $labelIDs;
    }

    /**
     * Returns label group by id.
     *
     * @param int $groupID
     * @return  ViewableLabelGroup|null
     */
    public function getLabelGroup($groupID)
    {
        return $this->labelGroups['groups'][$groupID] ?? null;
    }

    /**
     * Removes all assigned labels for given object ids.
     *
     * @param int $objectTypeID
     * @param int[] $objectIDs
     */
    public function removeLabels($objectTypeID, array $objectIDs)
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$objectTypeID]);
        $conditions->add("objectID IN (?)", [$objectIDs]);
        $sql = "DELETE FROM wcf1_label_object
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
    }
}
