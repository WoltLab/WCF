<?php

namespace wcf\system\bulk\processing\user;

use wcf\command\user\AddUserToGroups;
use wcf\data\user\User;

/**
 * Bulk processing action implementation for assigning users to user groups.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AssignToUserGroupsUserBulkProcessingAction extends AbstractUserGroupsUserBulkProcessingAction
{
    /**
     * @inheritDoc
     */
    public $inputName = 'assignToUserGroupIDs';

    #[\Override]
    protected function executeUserAction(User $user)
    {
        (new AddUserToGroups($user, $this->userGroupIDs))();
    }
}
