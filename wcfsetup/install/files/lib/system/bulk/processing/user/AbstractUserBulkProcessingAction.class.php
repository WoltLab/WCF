<?php

namespace wcf\system\bulk\processing\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\bulk\processing\AbstractBulkProcessingAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract implementation of a user bulk processing action.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractUserBulkProcessingAction extends AbstractBulkProcessingAction
{
    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        return new UserList();
    }

    /**
     * Returns all users who the active user can access due to their user group
     * association.
     *
     * @param UserList $userList
     * @return  User[]
     */
    protected function getAccessibleUsers(UserList $userList)
    {
        // fetch user group ids of all users
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('userID IN (?)', [$userList->getObjectIDs()]);

        $sql = "SELECT  userID, groupID
                FROM    wcf1_user_to_group
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $groupIDs = $statement->fetchMap('userID', 'groupID', false);

        $ownerGroupID = UserGroup::getOwnerGroupID();

        $users = [];
        foreach ($userList as $user) {
            if (empty($groupIDs[$user->userID])) {
                $users[$user->userID] = $user;
            } elseif ($ownerGroupID && \in_array($ownerGroupID, $groupIDs[$user->userID])) {
                // Bulk actions can never affect members of the owner group.
                continue;
            } elseif (UserGroup::isAccessibleGroup($groupIDs[$user->userID])) {
                $users[$user->userID] = $user;
            }
        }

        return $users;
    }
}
