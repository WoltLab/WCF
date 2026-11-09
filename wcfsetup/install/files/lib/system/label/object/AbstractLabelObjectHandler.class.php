<?php

namespace wcf\system\label\object;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\system\exception\SystemException;
use wcf\system\label\LabelHandler;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a label object handler.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractLabelObjectHandler extends SingletonFactory implements ILabelObjectHandler
{
    /**
     * list of available label groups
     * @var ViewableLabelGroup[]
     */
    protected $labelGroups = [];

    /**
     * object type name
     * @var string
     */
    protected $objectType = '';

    /**
     * object type id
     * @var int
     */
    protected $objectTypeID = 0;

    #[\Override]
    protected function init()
    {
        $this->labelGroups = LabelHandler::getInstance()->getLabelGroups();

        $objectType = LabelHandler::getInstance()->getObjectType($this->objectType);
        if ($objectType === null) {
            throw new SystemException("object type '" . $this->objectType . "' is invalid");
        }
        $this->objectTypeID = $objectType->objectTypeID;
    }

    #[\Override]
    public function getLabelGroupIDs(array $parameters = [])
    {
        return \array_keys($this->labelGroups);
    }

    /**
     * @return  ViewableLabelGroup[]
     */
    #[\Override]
    public function getLabelGroups(array $parameters = [])
    {
        $groupIDs = $this->getLabelGroupIDs($parameters);

        $data = [];
        foreach ($groupIDs as $groupID) {
            $data[$groupID] = $this->labelGroups[$groupID];
        }

        \uasort($data, [LabelGroup::class, 'sortLabelGroups']);

        return $data;
    }

    #[\Override]
    public function validateLabelIDs(array $labelIDs, string $optionName = '', bool $legacyReturnValue = true)
    {
        $optionID = 0;
        if (!empty($optionName)) {
            $optionID = LabelHandler::getInstance()->getOptionID($optionName);
            if ($optionID === null) {
                throw new SystemException("Cannot validate label permissions, option '" . $optionName . "' is unknown");
            }
        }

        $validationErrors = [];
        $satisfiedGroups = [];
        foreach ($labelIDs as $groupID => $labelID) {
            // label group id is unknown
            if (!isset($this->labelGroups[$groupID])) {
                $validationErrors[0] = 'invalid';
                continue;
            }

            if (\is_array($labelID)) {
                // only handlers that support multiple labels per group accept a list
                if (!($this instanceof IMultipleLabelsObjectHandler)) {
                    $validationErrors[$groupID] = 'invalid';
                    continue;
                }

                $groupLabelIDs = $labelID;
            } else {
                $groupLabelIDs = [$labelID];
            }

            if ($groupLabelIDs === []) {
                continue;
            }

            if (\count($groupLabelIDs) !== \count(\array_unique($groupLabelIDs))) {
                $validationErrors[$groupID] = 'invalid';
                continue;
            }

            if (\count($groupLabelIDs) > $this->getMaxSelection($this->labelGroups[$groupID])) {
                $validationErrors[$groupID] = 'tooMany';
                continue;
            }

            // label id is invalid for this group
            foreach ($groupLabelIDs as $groupLabelID) {
                if (!$this->labelGroups[$groupID]->isValid($groupLabelID)) {
                    $validationErrors[$groupID] = 'invalid';
                    continue 2;
                }
            }

            // check permission
            if (
                $optionID !== 0
                && (
                    $this->labelGroups[$groupID]->hasPermissions()
                    && !$this->labelGroups[$groupID]->getPermission($optionID)
                )
            ) {
                $validationErrors[0] = 'invalid';
                continue;
            }

            $satisfiedGroups[] = $groupID;
        }

        // check if required label groups were set
        foreach ($this->labelGroups as $labelGroup) {
            if ($labelGroup->forceSelection !== 0 && !\in_array($labelGroup->groupID, $satisfiedGroups, true)) {
                // check if group wasn't set, but is not accessible for this user anyway
                if ($labelGroup->hasPermissions() && !$labelGroup->getPermission($optionID)) {
                    continue;
                }

                $validationErrors[$labelGroup->groupID] = 'missing';
                continue;
            }
        }

        if ($legacyReturnValue) {
            // WCF 2.0: boolean true/false
            return $validationErrors === [];
        } else {
            // WCF 2.1+: detailed error report (empty array if validation passed)
            return $validationErrors;
        }
    }

    /**
     * Returns the maximum number of labels of the given label group that can
     * be assigned to a single object.
     *
     * @since 6.3
     */
    public function getMaxSelection(ViewableLabelGroup $labelGroup): int
    {
        return LabelHandler::getInstance()->getMaxSelection($labelGroup, $this->objectType);
    }

    #[\Override]
    public function setLabels(array $labelIDs, int $objectID, bool $validatePermissions = true)
    {
        LabelHandler::getInstance()->setLabels($labelIDs, $this->objectTypeID, $objectID, $validatePermissions);
    }

    #[\Override]
    public function removeLabels(int|array $objectID, bool $validatePermissions = true)
    {
        $objectIDs = (\is_array($objectID)) ? $objectID : [$objectID];
        LabelHandler::getInstance()->removeLabels($this->objectTypeID, $objectIDs);
    }

    #[\Override]
    public function getAssignedLabels(array $objectIDs, bool $validatePermissions = true)
    {
        return LabelHandler::getInstance()->getAssignedLabels($this->objectTypeID, $objectIDs, $validatePermissions);
    }
}
