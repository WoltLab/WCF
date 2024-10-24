<?php

namespace wcf\system\clipboard\action;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for user objects.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $actionClassActions = ['delete', 'resendActivationMail', 'confirmEmail', 'unconfirmEmail'];

    /**
     * @inheritDoc
     */
    protected $supportedActions = [
        'assignToGroup',
        'ban',
        'confirmEmail',
        'delete',
        'deleteUserContent',
        'enable',
        'exportMailAddress',
        'merge',
        'sendMail',
        'sendNewPassword',
        'resendActivationMail',
        'unconfirmEmail',
    ];

    /**
     * @inheritDoc
     */
    public function execute(array $objects, ClipboardAction $action)
    {
        $item = parent::execute($objects, $action);

        if ($item === null) {
            return;
        }

        // handle actions
        switch ($action->actionName) {
            case 'assignToGroup':
                $item->setURL(LinkHandler::getInstance()->getLink('UserAssignToGroup'));
                break;

            case 'delete':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.clipboard.item.com.woltlab.wcf.user.delete.confirmMessage',
                        [
                            'count' => $item->getCount(),
                        ]
                    )
                );
                break;

            case 'exportMailAddress':
                $item->setURL(LinkHandler::getInstance()->getLink('UserEmailAddressExport'));
                break;

            case 'merge':
                $item->setURL(LinkHandler::getInstance()->getLink('UserMerge'));
                break;

            case 'sendMail':
                $item->setURL(LinkHandler::getInstance()->getLink('UserMail'));
                break;

            case 'sendNewPassword':
                $item->addParameter(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.clipboard.item.com.woltlab.wcf.user.sendNewPassword.confirmMessage',
                        [
                            'count' => $item->getCount(),
                        ]
                    )
                );
                break;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return UserAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.woltlab.wcf.user';
    }

    /**
     * Returns the ids of the users which can be deleted.
     *
     * @return  int[]
     */
    protected function validateDelete()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canDeleteUser')) {
            return [];
        }

        return $this->__validateAccessibleGroups(\array_keys($this->objects));
    }

    /**
     * Returns the ids of the users which can be banned.
     *
     * @return  int[]
     */
    protected function validateBan()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
            return [];
        }

        $userIDs = [];
        foreach ($this->objects as $user) {
            if (!$user->banned) {
                $userIDs[] = $user->userID;
            }
        }

        return $this->__validateAccessibleGroups($userIDs);
    }

    /**
     * Validates accessible groups.
     *
     * @param int[] $userIDs
     * @param bool $ignoreOwnUser
     * @return  int[]
     */
    protected function __validateAccessibleGroups(array $userIDs, $ignoreOwnUser = true)
    {
        if ($ignoreOwnUser) {
            foreach ($userIDs as $index => $userID) {
                if ($userID == WCF::getUser()->userID) {
                    unset($userIDs[$index]);
                }
            }
        }

        // no valid users found
        if (empty($userIDs)) {
            return [];
        }

        // fetch user to group associations
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$userIDs]);

        $sql = "SELECT  userID, groupID
                FROM    wcf1_user_to_group
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $userToGroup = $statement->fetchMap('userID', 'groupID', false);

        // validate if user's group is accessible for current user
        foreach ($userIDs as $index => $userID) {
            if (!isset($userToGroup[$userID]) || !UserGroup::isAccessibleGroup($userToGroup[$userID])) {
                unset($userIDs[$index]);
            }
        }

        return \array_values($userIDs);
    }

    /**
     * Returns the ids of the users which can be marked as email confirmed.
     *
     * @return  int[]
     * @since   5.3
     */
    protected function validateConfirmEmail()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
            return [];
        }

        $userIDs = [];
        foreach ($this->objects as $user) {
            /** @var User $user */
            if (!$user->isEmailConfirmed()) {
                $userIDs[] = $user->userID;
            }
        }

        return $this->__validateAccessibleGroups($userIDs);
    }

    /**
     * Returns the ids of the users which can be unmarked as email confirmed.
     *
     * @return  int[]
     * @since   5.3
     */
    protected function validateUnconfirmEmail()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
            return [];
        }

        $userIDs = [];
        foreach ($this->objects as $user) {
            /** @var User $user */
            if ($user->isEmailConfirmed()) {
                $userIDs[] = $user->userID;
            }
        }

        return $this->__validateAccessibleGroups($userIDs);
    }

    /**
     * Returns the ids of the users which can be sent new passwords.
     *
     * @return  int[]
     */
    public function validateSendNewPassword()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canEditPassword')) {
            return [];
        }

        $userIDs = [];
        /** @var User $user */
        foreach ($this->objects as $user) {
            if (empty($user->authData)) {
                $userIDs[] = $user->userID;
            }
        }

        return $this->__validateAccessibleGroups($userIDs);
    }

    /**
     * Returns the ids of the users which can be enabled.
     *
     * @return  int[]
     * @since   3.0
     */
    protected function validateEnable()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
            return [];
        }

        $userIDs = [];
        foreach ($this->objects as $user) {
            /** @var User $user */
            if ($user->pendingActivation()) {
                $userIDs[] = $user->userID;
            }
        }

        return $userIDs;
    }

    /**
     * Returns the ids of the users which can be merge.
     *
     * @return  int[]
     * @since   3.0
     */
    protected function validateMerge()
    {
        // check permissions
        if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
            return [];
        }

        $userIDs = \array_keys($this->objects);
        if (\count($userIDs) < 2) {
            return [];
        }

        return $userIDs;
    }

    /**
     * Returns the ids of the users which aren't activated.
     *
     * @return  int[]
     * @since   5.2
     */
    protected function validateResendActivationMail()
    {
        // check permissions
        if (
            !WCF::getSession()->getPermission('admin.user.canEnableUser')
            || !(REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER)
        ) {
            return [];
        }

        $userIDs = [];
        foreach ($this->objects as $user) {
            /** @var User $user */
            if ($user->canEmailConfirm()) {
                $userIDs[] = $user->userID;
            }
        }

        return $userIDs;
    }

    /**
     * Returns the ids of the users whose contents can be deleted.
     *
     * @return      int[]
     * @since       5.4
     */
    protected function validateDeleteUserContent()
    {
        if (!WCF::getSession()->getPermission('admin.user.canDeleteUser')) {
            return [];
        }

        return $this->__validateAccessibleGroups(\array_keys($this->objects));
    }

    /**
     * @return      int[]
     * @since       6.0
     */
    protected function validateSendMail(): array
    {
        if (!WCF::getSession()->getPermission('admin.user.canMailUser')) {
            return [];
        }

        return \array_keys($this->objects);
    }

    /**
     * @return      int[]
     * @since       6.0
     */
    protected function validateExportMailAddress(): array
    {
        if (!WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
            return [];
        }

        return \array_keys($this->objects);
    }
}
