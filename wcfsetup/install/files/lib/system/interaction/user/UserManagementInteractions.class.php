<?php

namespace wcf\system\interaction\user;

use wcf\acp\form\UserEditForm;
use wcf\action\UserBanAction;
use wcf\action\UserDisableAvatarAction;
use wcf\action\UserDisableCoverPhotoAction;
use wcf\action\UserDisableSignatureAction;
use wcf\data\DatabaseObject;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\event\interaction\user\UserManagementInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\interaction\InteractionEffect;
use wcf\system\interaction\RpcInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for the management context menu in user profiles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserManagementInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new FormBuilderDialogInteraction(
                'ban',
                LinkHandler::getInstance()->getControllerLink(UserBanAction::class, ['id' => '%s']),
                'wcf.user.ban',
                static fn(UserProfile $user) => $user->banned === 0
                    && $user->canBan(),
                InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'unban',
                'core/users/%s/unban',
                'wcf.user.unban',
                isAvailableCallback: static fn(UserProfile $user) => $user->banned === 1
                    && $user->canBan(),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new FormBuilderDialogInteraction(
                'disable-avatar',
                LinkHandler::getInstance()->getControllerLink(UserDisableAvatarAction::class, ['id' => '%s']),
                'wcf.user.disableAvatar',
                static fn(UserProfile $user) => $user->disableAvatar === 0
                    && $user->canDisableAvatar(),
                InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'enable-avatar',
                'core/users/%s/enable-avatar',
                'wcf.user.enableAvatar',
                isAvailableCallback: static fn(UserProfile $user) => $user->disableAvatar === 1
                    && $user->canDisableAvatar(),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new FormBuilderDialogInteraction(
                'disable-cover-photo',
                LinkHandler::getInstance()->getControllerLink(UserDisableCoverPhotoAction::class, ['id' => '%s']),
                'wcf.user.disableCoverPhoto',
                static fn(UserProfile $user) => $user->disableCoverPhoto === 0
                    && $user->canDisableCoverPhoto(),
                InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'enable-cover-photo',
                'core/users/%s/enable-cover-photo',
                'wcf.user.enableCoverPhoto',
                isAvailableCallback: static fn(UserProfile $user) => $user->disableCoverPhoto === 1
                    && $user->canDisableCoverPhoto(),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new FormBuilderDialogInteraction(
                'disable-signature',
                LinkHandler::getInstance()->getControllerLink(UserDisableSignatureAction::class, ['id' => '%s']),
                'wcf.user.disableSignature',
                static fn(UserProfile $user) => $user->disableSignature === 0
                    && $user->canDisableSignature(),
                InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'enable-signature',
                'core/users/%s/enable-signature',
                'wcf.user.enableSignature',
                isAvailableCallback: static fn(UserProfile $user) => $user->disableSignature === 1
                    && $user->canDisableSignature(),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'enable',
                'core/users/%s/enable',
                'wcf.acp.user.enable',
                isAvailableCallback: static fn(UserProfile $user) => $user->pendingActivation()
                    && $user->canEnable(),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'disable',
                'core/users/%s/disable',
                'wcf.acp.user.disable',
                isAvailableCallback: static fn(UserProfile $user) => !$user->pendingActivation()
                    && $user->canEnable(),
                interactionEffect: InteractionEffect::ReloadPage
            ),
        ]);
        if (WCF::getSession()->hasPermission('admin.general.canUseAcp') && WCF::getSession()->hasPermission('admin.user.canEditUser')) {
            $this->addInteraction(
                new class(
                    'edit',
                    static fn(UserProfile $user) => UserGroup::isAccessibleGroup($user->getGroupIDs()) && WCF::getUser()->userID !== $user->userID
                ) extends AbstractInteraction {
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
