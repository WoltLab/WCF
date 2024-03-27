<?php

namespace wcf\system\acl;

use wcf\data\acl\option\ACLOption;
use wcf\data\acl\option\ACLOptionList;
use wcf\data\acl\option\category\ACLOptionCategory;
use wcf\data\acl\option\category\ACLOptionCategoryList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\cache\builder\ACLOptionCategoryCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Handles ACL permissions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACLHandler extends SingletonFactory
{
    /**
     * indicates if assignment of variables is disabled
     * @var int
     */
    protected $assignVariablesDisabled = false;

    /**
     * list of available object types
     * @var array
     */
    protected $availableObjectTypes = [];

    /**
     * list of acl option categories sorted by their object type id and name
     * @var ACLOptionCategory[][]
     */
    protected $categories = [];

    /**
     * explicitly read acl values grouped by object type id
     * @var     array
     * @see     ACLHandler::readValues()
     * @since   5.2
     */
    protected $__readValues = [];

    /**
     * Assigns the acl values to the template.
     *
     * @param int $objectTypeID
     */
    public function assignVariables($objectTypeID)
    {
        if (WCF::getTPL()->get('aclValues') === null) {
            WCF::getTPL()->assign('aclValues', []);
        }

        $values = null;
        if (\array_key_exists($objectTypeID, $this->__readValues)) {
            $values = $this->__readValues[$objectTypeID];
        } elseif (isset($_POST['aclValues'])) {
            $values = $_POST['aclValues'];
        }

        if (!$this->assignVariablesDisabled && $values !== null) {
            $data = $this->getPermissions($objectTypeID, [], null, true);

            $users = [];
            foreach ($values as $type => $optionData) {
                $optionData = JSON::decode($optionData);
                if ($type === 'user') {
                    $users = User::getUsers(\array_keys($optionData));
                }

                $values[$type] = [
                    'label' => [],
                    'option' => [],
                ];

                foreach ($optionData as $typeID => $optionValues) {
                    foreach ($optionValues as $optionID => $optionValue) {
                        if (!isset($data['options'][$optionID])) {
                            unset($optionValues[$optionID]);
                        }
                    }

                    if (empty($optionValues)) {
                        continue;
                    }

                    $values[$type]['option'][$typeID] = $optionValues;

                    if ($type === 'group') {
                        $values[$type]['label'][$typeID] = UserGroup::getGroupByID($typeID)->getName();
                    } else {
                        $values[$type]['label'][$typeID] = $users[$typeID]->username;
                    }
                }
            }

            $values['options'] = $data['options'];
            $values['categories'] = $data['categories'];

            WCF::getTPL()->append('aclValues', [
                $objectTypeID => $values,
            ]);
        }
    }

    /**
     * Disables assignment of variables to template.
     */
    public function disableAssignVariables()
    {
        $this->assignVariablesDisabled = true;
    }

    /**
     * Enables assignment of variables to template.
     */
    public function enableAssignVariables()
    {
        $this->assignVariablesDisabled = false;
    }

    /**
     * Reads the values for the given object type id.
     *
     * Note: This method primarily only exists for form builder. If you are not
     * using form builder, you do not need this method.
     *
     * @param int $objectTypeID
     * @param array|null $valuesSource array used to read the values from (if `null`, `$_POST['aclValues']` is used)
     * @since   5.2
     */
    public function readValues($objectTypeID, ?array $valuesSource = null)
    {
        $this->__readValues[$objectTypeID] = [];

        if ($valuesSource === null && isset($_POST['aclValues'])) {
            $valuesSource = $_POST['aclValues'];
        }

        if (isset($valuesSource)) {
            $options = ACLOption::getOptions($objectTypeID)->getObjects();

            foreach (['group', 'user'] as $type) {
                if (isset($valuesSource[$type])) {
                    $this->__readValues[$objectTypeID][$type] = [];

                    foreach ($valuesSource[$type] as $typeID => $optionData) {
                        $optionData = JSON::decode($optionData);

                        $this->__readValues[$objectTypeID][$type][$typeID] = [];

                        foreach ($optionData as $optionID => $optionValue) {
                            if (isset($options[$optionID])) {
                                $this->__readValues[$objectTypeID][$type][$typeID][$optionID] = $optionValue;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Resets the acl values read by `readValues()` for the given object type id.
     *
     * Note: This method primarily only exists for form builder. If you are not
     * using form builder, you do not need this method.
     *
     * @param int $objectTypeID
     * @since   5.2
     */
    public function resetValues($objectTypeID)
    {
        $this->__readValues[$objectTypeID] = null;
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.acl');
        $this->categories = ACLOptionCategoryCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns the id of the given acl object type.
     *
     * @param string $objectType
     * @return  int
     * @throws  SystemException
     */
    public function getObjectTypeID($objectType)
    {
        if (!isset($this->availableObjectTypes[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "'");
        }

        return $this->availableObjectTypes[$objectType]->objectTypeID;
    }

    /**
     * Returns the acl option category with the given object type id and name
     * or `null` if no such category exists.
     *
     * @param int $objectTypeID
     * @param string $categoryName
     * @return  ACLOptionCategory|null
     */
    public function getCategory($objectTypeID, $categoryName)
    {
        return $this->categories[$objectTypeID][$categoryName] ?? null;
    }

    /**
     * Saves acl for a given object.
     *
     * @param int $objectID
     * @param int $objectTypeID
     */
    public function save($objectID, $objectTypeID)
    {
        // get options
        $optionList = ACLOption::getOptions($objectTypeID);

        $this->replaceValues($optionList, 'group', $objectID);
        $this->replaceValues($optionList, 'user', $objectID);
    }

    /**
     * Replaces values for given type and object.
     *
     * @param ACLOptionList $optionList
     * @param string $type
     * @param int $objectID
     */
    protected function replaceValues(ACLOptionList $optionList, $type, $objectID)
    {
        $options = $optionList->getObjects();

        // remove previous values
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("optionID IN (?)", [\array_keys($options)]);
        $conditions->add("objectID = ?", [$objectID]);

        $sql = "DELETE FROM wcf" . WCF_N . "_acl_option_to_" . $type . "
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());

        $objectTypeID = \reset($options)->objectTypeID;

        // add new values if given
        $values = [];
        if (isset($this->__readValues[$objectTypeID]) && isset($this->__readValues[$objectTypeID][$type])) {
            $values = JSON::decode($this->__readValues[$objectTypeID][$type]);
        } elseif (isset($_POST['aclValues']) && isset($_POST['aclValues'][$type])) {
            $values = JSON::decode($_POST['aclValues'][$type]);
        }

        $sql = "INSERT INTO wcf" . WCF_N . "_acl_option_to_" . $type . "
                            (optionID, objectID, " . $type . "ID, optionValue)
                VALUES      (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        WCF::getDB()->beginTransaction();
        foreach ($values as $typeID => $optionData) {
            foreach ($optionData as $optionID => $optionValue) {
                // ignore invalid option ids
                if (!isset($options[$optionID])) {
                    continue;
                }

                $statement->execute([
                    $optionID,
                    $objectID,
                    $typeID,
                    $optionValue,
                ]);
            }
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Returns a list of permissions by object type id.
     *
     * @param int $objectTypeID
     * @param array $objectIDs
     * @param string $categoryName
     * @param bool $settingsView
     * @return  array
     */
    public function getPermissions($objectTypeID, array $objectIDs, $categoryName = '', $settingsView = false)
    {
        $optionList = $this->getOptions($objectTypeID, $categoryName);

        $data = [
            'options' => $optionList,
            'group' => [],
            'user' => [],
        ];

        if (!empty($objectIDs)) {
            $this->getValues($optionList, 'group', $objectIDs, $data, $settingsView);
            $this->getValues($optionList, 'user', $objectIDs, $data, $settingsView);
        }

        // use alternative data structure for settings
        if ($settingsView) {
            $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);

            $data['options'] = [];
            $data['categories'] = [];

            if (\count($optionList)) {
                $categoryNames = [];
                foreach ($optionList as $option) {
                    $data['options'][$option->optionID] = [
                        'categoryName' => $option->categoryName,
                        'label' => WCF::getLanguage()->getDynamicVariable('wcf.acl.option.' . $objectType->objectType . '.' . $option->optionName),
                        'optionName' => $option->optionName,
                    ];

                    if (!\in_array($option->categoryName, $categoryNames)) {
                        $categoryNames[] = $option->categoryName;
                    }
                }

                // load categories
                $categoryList = new ACLOptionCategoryList();
                $categoryList->getConditionBuilder()->add("acl_option_category.categoryName IN (?)", [$categoryNames]);
                $categoryList->getConditionBuilder()->add("acl_option_category.objectTypeID = ?", [$objectTypeID]);
                $categoryList->readObjects();

                foreach ($categoryList as $category) {
                    $data['categories'][$category->categoryName] = WCF::getLanguage()->get('wcf.acl.option.category.' . $objectType->objectType . '.' . $category->categoryName);
                }
            }
        }

        return $data;
    }

    /**
     * Fetches ACL option values by type.
     *
     * @param ACLOptionList $optionList
     * @param string $type
     * @param array $objectIDs
     * @param array $data
     * @param bool $settingsView
     */
    protected function getValues(ACLOptionList $optionList, $type, array $objectIDs, array &$data, $settingsView)
    {
        $data[$type] = [];
        $optionsIDs = [];
        foreach ($optionList as $option) {
            $optionsIDs[] = $option->optionID;
        }

        // category matched no options
        if (empty($optionsIDs)) {
            return;
        }

        $columnID = $type . 'ID';
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("optionID IN (?)", [$optionsIDs]);
        $conditions->add("objectID IN (?)", [$objectIDs]);
        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_acl_option_to_" . $type . "
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());
        while ($row = $statement->fetchArray()) {
            if (!isset($data[$type][$row['objectID']])) {
                $data[$type][$row['objectID']] = [];
            }

            if (!isset($data[$type][$row['objectID']][$row[$columnID]])) {
                $data[$type][$row['objectID']][$row[$columnID]] = [];
            }

            $data[$type][$row['objectID']][$row[$columnID]][$row['optionID']] = $row['optionValue'];
        }

        // use alternative data structure for settings
        if ($settingsView) {
            $objectID = \current($objectIDs);
            if (!isset($data[$type][$objectID])) {
                $data[$type][$objectID] = [];
            }

            // build JS-compliant structure
            $data[$type] = [
                'label' => [],
                'option' => $data[$type][$objectID],
            ];

            // load labels
            if (!empty($data[$type]['option'])) {
                $conditions = new PreparedStatementConditionBuilder();

                if ($type == 'group') {
                    $conditions->add("groupID IN (?)", [\array_keys($data[$type]['option'])]);
                    $sql = "SELECT  groupID, groupName
                            FROM    wcf" . WCF_N . "_user_group
                            " . $conditions;
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute($conditions->getParameters());

                    while ($row = $statement->fetchArray()) {
                        $data['group']['label'][$row['groupID']] = WCF::getLanguage()->get($row['groupName']);
                    }
                } else {
                    $conditions->add("userID IN (?)", [\array_keys($data[$type]['option'])]);
                    $sql = "SELECT  userID, username
                            FROM    wcf" . WCF_N . "_user
                            " . $conditions;
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute($conditions->getParameters());
                    $data['user']['label'] = $statement->fetchMap('userID', 'username');
                }
            }
        }
    }

    /**
     * Returns a list of options by object type id.
     *
     * @param int $objectTypeID
     * @param string $categoryName
     * @return  ACLOptionList
     */
    public function getOptions($objectTypeID, $categoryName = '')
    {
        $optionList = new ACLOptionList();
        if (!empty($categoryName)) {
            if (\str_ends_with($categoryName, '.*')) {
                $categoryName = \mb_substr($categoryName, 0, -1) . '%';
                $optionList->getConditionBuilder()->add("acl_option.categoryName LIKE ?", [$categoryName]);
            } else {
                $optionList->getConditionBuilder()->add("acl_option.categoryName = ?", [$categoryName]);
            }
        }
        $optionList->getConditionBuilder()->add("acl_option.objectTypeID = ?", [$objectTypeID]);
        $optionList->readObjects();

        return $optionList;
    }

    /**
     * Removes ACL values from database.
     *
     * @param int $objectTypeID
     * @param int[] $objectIDs
     * @param ACLOptionCategory $category
     */
    public function removeValues($objectTypeID, array $objectIDs, ?ACLOptionCategory $category = null)
    {
        $optionList = $this->getOptions($objectTypeID, $category);
        $options = $optionList->getObjects();

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("optionID IN (?)", [\array_keys($options)]);
        $conditions->add("objectID IN (?)", [$objectIDs]);

        WCF::getDB()->beginTransaction();
        foreach (['group', 'user'] as $type) {
            $sql = "DELETE FROM wcf" . WCF_N . "_acl_option_to_" . $type . "
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
        }
        WCF::getDB()->commitTransaction();
    }
}
