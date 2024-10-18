<?php

namespace wcf\data\user\group;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\cache\builder\UserGroupCacheBuilder;
use wcf\system\cache\builder\UserGroupPermissionCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit user groups.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserGroup   getDecoratedObject()
 * @mixin   UserGroup
 */
class UserGroupEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserGroup::class;

    /**
     * @inheritDoc
     * @return  UserGroup
     */
    public static function create(array $parameters = [])
    {
        /** @var UserGroup $group */
        $group = parent::create($parameters);

        // update accessible groups
        self::updateAccessibleGroups($group->groupID);

        return $group;
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = [])
    {
        $returnValue = parent::deleteAll($objectIDs);

        // remove user to group assignments
        self::removeGroupAssignments($objectIDs);

        // remove group option values
        self::removeOptionValues($objectIDs);

        foreach ($objectIDs as $objectID) {
            self::updateAccessibleGroups($objectID, true);
        }

        return $returnValue;
    }

    /**
     * Removes user to group assignments.
     *
     * @param array $groupIDs
     */
    protected static function removeGroupAssignments(array $groupIDs)
    {
        if (empty($groupIDs)) {
            return;
        }

        $sql = "DELETE FROM wcf1_user_to_group
                WHERE       groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($groupIDs as $groupID) {
            $statement->execute([$groupID]);
        }
    }

    /**
     * Removes group option values.
     *
     * @param array $groupIDs
     */
    protected static function removeOptionValues(array $groupIDs)
    {
        if (empty($groupIDs)) {
            return;
        }

        $sql = "DELETE FROM wcf1_user_group_option_value
                WHERE       groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($groupIDs as $groupID) {
            $statement->execute([$groupID]);
        }
    }

    /**
     * Updates group options.
     *
     * @param array $groupOptions
     */
    public function updateGroupOptions(array $groupOptions = [])
    {
        WCF::getDB()->beginTransaction();
        // delete old group options
        $sql = "DELETE FROM wcf1_user_group_option_value
                WHERE       groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->groupID]);

        // insert new options
        $sql = "INSERT INTO wcf1_user_group_option_value
                            (groupID, optionID, optionValue)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($groupOptions as $id => $value) {
            $statement->execute([$this->groupID, $id, $value]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Updates the value from the accessiblegroups option.
     *
     * @param int $groupID this group is added or deleted in the value
     * @param bool $delete flag for group deletion
     * @throws  SystemException
     */
    protected static function updateAccessibleGroups($groupID, $delete = false)
    {
        $sql = "SELECT  optionID
                FROM    wcf1_user_group_option
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['admin.user.accessibleGroups']);
        $optionID = $statement->fetchSingleColumn();

        if (!$optionID) {
            throw new SystemException("Unable to find 'admin.user.accessibleGroups' user option");
        }

        $ownerGroupID = UserGroup::getOwnerGroupID();

        $userGroupList = new UserGroupList();
        $userGroupList->getConditionBuilder()->add('user_group.groupID <> ?', [$groupID]);
        if ($ownerGroupID) {
            $userGroupList->getConditionBuilder()->add('user_group.groupID <> ?', [$ownerGroupID]);
        }
        $userGroupList->readObjects();
        $groupIDs = [];
        foreach ($userGroupList as $userGroup) {
            $groupIDs[] = $userGroup->groupID;
        }

        $sql = "UPDATE  wcf1_user_group_option_value
                SET     optionValue = ?
                WHERE   groupID = ?
                    AND optionID = ?";
        $updateStatement = WCF::getDB()->prepare($sql);

        $sql = "SELECT  groupID, optionValue
                FROM    wcf1_user_group_option_value
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$optionID]);
        while ($row = $statement->fetchArray()) {
            $valueIDs = \array_filter(
                \explode(',', $row['optionValue']),
                static function ($groupID) use ($ownerGroupID) {
                    return $groupID != $ownerGroupID;
                }
            );

            if ($delete) {
                $valueIDs = \array_filter($valueIDs, static function ($item) use ($groupID) {
                    return $item != $groupID;
                });
            } else {
                if (\count(\array_diff($groupIDs, $valueIDs)) == 0) {
                    $valueIDs[] = $groupID;
                }
            }

            if ($row['groupID'] == $ownerGroupID) {
                $valueIDs[] = $ownerGroupID;
            }

            $updateStatement->execute([\implode(',', $valueIDs), $row['groupID'], $optionID]);
        }
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        // Clear group cache.
        UserGroupCacheBuilder::getInstance()->reset();
        UserGroupPermissionCacheBuilder::getInstance()->reset();

        // https://github.com/WoltLab/WCF/issues/4045
        UserGroupAssignmentCacheBuilder::getInstance()->reset();

        // Clear cached group assignments.
        UserStorageHandler::getInstance()->resetAll('groupIDs');
    }
}
