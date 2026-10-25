<?php

namespace wcf\system\bulk\processing\user;

use wcf\command\user\RemoveUserFromGroups;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for removing users from user groups.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class RemoveFromUserGroupsUserBulkProcessingAction extends AbstractUserGroupsUserBulkProcessingAction
{
    /**
     * @inheritDoc
     */
    public $inputName = 'removeFromUserGroupIDs';

    #[\Override]
    protected function executeUserAction(User $user)
    {
        new RemoveUserFromGroups($user, $this->userGroupIDs)();
    }

    #[\Override]
    public function getObjectList()
    {
        $userList = parent::getObjectList();

        // the active user may not remove themselves from any user group
        // to avoid potential permission issues
        $userList->getConditionBuilder()->add('user_table.userID <> ?', [WCF::getUser()->userID]);

        return $userList;
    }
}
