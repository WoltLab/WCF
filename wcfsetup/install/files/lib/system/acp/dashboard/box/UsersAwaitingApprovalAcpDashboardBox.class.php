<?php

namespace wcf\system\acp\dashboard\box;

use wcf\data\user\User;
use wcf\data\user\UserProfileList;
use wcf\system\WCF;

/**
 * ACP dashboard box listing users waiting for approval.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UsersAwaitingApprovalAcpDashboardBox extends AbstractAcpDashboardBox
{
    private UserProfileList $userList;
    private int $usersAwaitingApproval;

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.user.canSearchUser')
            && REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN;
    }

    #[\Override]
    public function hasContent(): bool
    {
        return $this->getUsersAwaitingApproval() !== 0;
    }

    public function getTitle(): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.acp.dashboard.box.usersAwaitingApproval', [
            'usersAwaitingApproval' => $this->getUsersAwaitingApproval(),
        ]);
    }

    public function getContent(): string
    {
        $userList = $this->getUserList();
        $userList->readObjects();

        return WCF::getTPL()->fetch('usersAwaitingApprovalAcpDashboardBox', 'wcf', [
            'users' => $userList->getObjects(),
            'usersAwaitingApproval' => $this->getUsersAwaitingApproval(),
        ]);
    }

    public function getName(): string
    {
        return 'com.woltlab.wcf.usersAwaitingApproval';
    }

    private function getUserList(): UserProfileList
    {
        if (!isset($this->userList)) {
            $this->userList = new UserProfileList();
            $this->userList->sqlLimit = 10;
            $this->userList->getConditionBuilder()->add('banned = ?', [0]);
            $this->userList->getConditionBuilder()->add('activationCode <> ?', [0]);
            if (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) {
                $this->userList->getConditionBuilder()->add('emailConfirmed IS NULL');
            }
        }

        return $this->userList;
    }

    private function getUsersAwaitingApproval(): int
    {
        if (!isset($this->usersAwaitingApproval)) {
            $this->usersAwaitingApproval = $this->getUserList()->countObjects();
        }

        return $this->usersAwaitingApproval;
    }
}
