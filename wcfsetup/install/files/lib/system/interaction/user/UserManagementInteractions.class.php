<?php

namespace wcf\system\interaction\user;

use wcf\acp\form\UserEditForm;
use wcf\action\UserBanAction;
use wcf\action\UserDisableAvatarAction;
use wcf\action\UserDisableCoverPhotoAction;
use wcf\action\UserDisableSignatureAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\event\interaction\user\UserManagementInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\interaction\LinkInteraction;
use wcf\system\interaction\RpcInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Interaction provider for the management context menu in user profiles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserManagementInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteraction(
            new FormBuilderDialogInteraction(
                'ban',
                LinkHandler::getInstance()->getControllerLink(UserBanAction::class, [
                    'id' => '%s',
                ]),
                'wcf.user.ban',
                static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->banned === 0;
                },
            )
        );
        $this->addInteraction(
            new RpcInteraction(
                'unban',
                'core/users/%s/unban',
                'wcf.user.unban',
                isAvailableCallback: static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->banned === 1;
                },
            )
        );
        
        $this->addInteraction(
            new FormBuilderDialogInteraction(
                'disable-avatar',
                LinkHandler::getInstance()->getControllerLink(UserDisableAvatarAction::class, [
                    'id' => '%s',
                ]),
                'wcf.user.disableAvatar',
                static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->disableAvatar === 0;
                },
            )
        );
        $this->addInteraction(
            new RpcInteraction(
                'enable-avatar',
                'core/users/%s/enable-avatar',
                'wcf.user.enableAvatar',
                isAvailableCallback: static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->disableAvatar === 1;
                },
            )
        );
        
        $this->addInteraction(
            new FormBuilderDialogInteraction(
                'disable-signature',
                LinkHandler::getInstance()->getControllerLink(UserDisableSignatureAction::class, [
                    'id' => '%s',
                ]),
                'wcf.user.disableSignature',
                static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->disableSignature === 0;
                },
            )
        );
        $this->addInteraction(
            new RpcInteraction(
                'enable-signature',
                'core/users/%s/enable-signature',
                'wcf.user.enableSignature',
                isAvailableCallback: static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->disableSignature === 1;
                },
            )
        );

        $this->addInteraction(
            new FormBuilderDialogInteraction(
                'disable-cover-photo',
                LinkHandler::getInstance()->getControllerLink(UserDisableCoverPhotoAction::class, [
                    'id' => '%s',
                ]),
                'wcf.user.disableCoverPhoto',
                static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->disableCoverPhoto === 0;
                },
            )
        );
        $this->addInteraction(
            new RpcInteraction(
                'enable-cover-photo',
                'core/users/%s/enable-cover-photo',
                'wcf.user.enableCoverPhoto',
                isAvailableCallback: static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->disableCoverPhoto === 1;
                },
            )
        );

        $this->addInteraction(
            new RpcInteraction(
                'disable',
                'core/users/%s/disable',
                'wcf.acp.user.disable',
                isAvailableCallback: static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return !$user->pendingActivation();
                },
            )
        );
        $this->addInteraction(
            new RpcInteraction(
                'enable',
                'core/users/%s/enable',
                'wcf.acp.user.enable',
                isAvailableCallback: static function (UserProfile $user): bool {
                    if (WCF::getUser()->userID === $user->userID) {
                        return false;
                    }
                    if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
                        return false;
                    }
                    if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                        return false;
                    }

                    return $user->pendingActivation();
                },
            )
        );

        if (WCF::getSession()->getPermission('admin.general.canUseAcp') && WCF::getSession()->getPermission('admin.user.canEditUser')) {
            $this->addInteraction(
                new class('edit', static fn (UserProfile $user) => UserGroup::isAccessibleGroup($user->getGroupIDs()) && WCF::getUser()->userID !== $user->userID) extends AbstractInteraction {
                    #[\Override]
                    public function render(DatabaseObject $object): string
                    {
                        return \sprintf(
                            '<a href="%s">%s</a>',
                            StringUtil::encodeHTML(
                                LinkHandler::getInstance()->getControllerLink(UserEditForm::class, ['id' => $object->getObjectID()])
                            ),
                            WCF::getLanguage()->get('wcf.user.edit')
                        );
                    }
                }
            );
        }

        EventHandler::getInstance()->fire(
            new UserManagementInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserProfile::class;
    }
}
