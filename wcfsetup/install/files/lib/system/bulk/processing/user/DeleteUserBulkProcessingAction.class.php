<?php

namespace wcf\system\bulk\processing\user;

use wcf\command\user\DeleteUser;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for deleting users.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DeleteUserBulkProcessingAction extends AbstractUserBulkProcessingAction
{
    #[\Override]
    public function executeAction(DatabaseObjectList $objectList)
    {
        $users = $this->getAccessibleUsers($objectList);

        foreach ($users as $user) {
            new DeleteUser($user)();
        }
    }

    #[\Override]
    public function getObjectList()
    {
        $userList = parent::getObjectList();

        // deny self deletion
        $userList->getConditionBuilder()->add('user_table.userID <> ?', [WCF::getUser()->userID]);

        return $userList;
    }

    #[\Override]
    public function canRunInWorker(): bool
    {
        return true;
    }
}
