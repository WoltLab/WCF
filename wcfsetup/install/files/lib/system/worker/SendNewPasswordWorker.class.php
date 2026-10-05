<?php

namespace wcf\system\worker;

use wcf\acp\page\UserListPage;
use wcf\command\user\SendNewPassword;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for sending new passwords.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use the `SendNewPassword` command or the `core/users/{id}/send-new-password` endpoint.
 */
class SendNewPasswordWorker extends AbstractWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 20;

    #[\Override]
    public function countObjects()
    {
        $userList = new UserList();
        $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$this->parameters['userIDs']]);

        $this->count = $userList->countObjects();
    }

    #[\Override]
    public function execute()
    {
        $userList = new UserList();
        $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$this->parameters['userIDs']]);
        $userList->sqlLimit = $this->limit;
        $userList->sqlOffset = $this->limit * $this->loopCount;
        $userList->readObjects();

        foreach ($userList as $user) {
            (new SendNewPassword($user))();
        }
    }

    #[\Override]
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getControllerLink(UserListPage::class);
    }

    #[\Override]
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.user.canEditPassword']);

        if (
            !isset($this->parameters['userIDs'])
            || !\is_array($this->parameters['userIDs'])
            || $this->parameters['userIDs'] === []
        ) {
            throw new SystemException("'userIDs' parameter is missing or invalid");
        }

        // This worker is invokable through `WorkerProxyAction` with arbitrary
        // parameters, therefore it must enforce the group accessibility itself
        // instead of relying on the validation of the clipboard action.
        $userList = new UserList();
        $userList->setObjectIDs($this->parameters['userIDs']);
        $userList->readObjects();

        foreach ($userList as $user) {
            if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                throw new PermissionDeniedException();
            }

            if ($user->authData !== '') {
                throw new PermissionDeniedException();
            }
        }
    }
}
