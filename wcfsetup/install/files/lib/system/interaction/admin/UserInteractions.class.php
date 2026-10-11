<?php

namespace wcf\system\interaction\admin;

use wcf\acp\action\UserExportGdprAction;
use wcf\acp\form\UserMailForm;
use wcf\action\UserBanAction;
use wcf\action\UserDisableAvatarAction;
use wcf\action\UserDisableCoverPhotoAction;
use wcf\action\UserDisableSignatureAction;
use wcf\data\user\User;
use wcf\event\interaction\admin\UserInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\Divider;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\interaction\InteractionEffect;
use wcf\system\interaction\LinkableObjectInteraction;
use wcf\system\interaction\LinkInteraction;
use wcf\system\interaction\RpcInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Interaction provider for the user administration.
 *
 * Changes of the state of a user reload the affected row of the user list or
 * the profile header of the user edit page. Disconnecting a third party login
 * reloads the page, because it affects the form of the user edit page.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new LinkableObjectInteraction('view', 'wcf.acp.user.action.viewProfile'),
            new Divider(),
            new RpcInteraction(
                'enable',
                'core/users/%s/enable',
                'wcf.acp.user.enable',
                isAvailableCallback: static fn(User $user) => $user->pendingActivation()
                    && $user->canEnable()
            ),
            new RpcInteraction(
                'disable',
                'core/users/%s/disable',
                'wcf.acp.user.disable',
                isAvailableCallback: static fn(User $user) => !$user->pendingActivation()
                    && $user->canEnable()
            ),
            new RpcInteraction(
                'confirm-email',
                'core/users/%s/confirm-email',
                'wcf.acp.user.action.confirmEmail',
                isAvailableCallback: static fn(User $user) => !$user->isEmailConfirmed()
                    && $user->canEnable()
            ),
            new RpcInteraction(
                'unconfirm-email',
                'core/users/%s/unconfirm-email',
                'wcf.acp.user.action.unconfirmEmail',
                isAvailableCallback: static fn(User $user) => $user->isEmailConfirmed()
                    && $user->canEnable()
            ),
            new RpcInteraction(
                'resend-activation-mail',
                'core/users/%s/resend-activation-mail',
                'wcf.acp.user.action.resendActivationMail',
                isAvailableCallback: static fn(User $user) => $user->canResendActivationMail()
            ),
            new FormBuilderDialogInteraction(
                'ban',
                LinkHandler::getInstance()->getControllerLink(UserBanAction::class, ['id' => '%s']),
                'wcf.acp.user.ban',
                static fn(User $user) => $user->banned === 0
                    && $user->canBan()
            ),
            new RpcInteraction(
                'unban',
                'core/users/%s/unban',
                'wcf.acp.user.unban',
                isAvailableCallback: static fn(User $user) => $user->banned === 1
                    && $user->canBan()
            ),
            new FormBuilderDialogInteraction(
                'disable-avatar',
                LinkHandler::getInstance()->getControllerLink(UserDisableAvatarAction::class, ['id' => '%s']),
                'wcf.user.disableAvatar',
                static fn(User $user) => $user->disableAvatar === 0
                    && $user->canDisableAvatar()
            ),
            new RpcInteraction(
                'enable-avatar',
                'core/users/%s/enable-avatar',
                'wcf.user.enableAvatar',
                isAvailableCallback: static fn(User $user) => $user->disableAvatar === 1
                    && $user->canDisableAvatar()
            ),
            new FormBuilderDialogInteraction(
                'disable-cover-photo',
                LinkHandler::getInstance()->getControllerLink(UserDisableCoverPhotoAction::class, ['id' => '%s']),
                'wcf.user.disableCoverPhoto',
                static fn(User $user) => $user->disableCoverPhoto === 0
                    && $user->canDisableCoverPhoto()
            ),
            new RpcInteraction(
                'enable-cover-photo',
                'core/users/%s/enable-cover-photo',
                'wcf.user.enableCoverPhoto',
                isAvailableCallback: static fn(User $user) => $user->disableCoverPhoto === 1
                    && $user->canDisableCoverPhoto()
            ),
            new FormBuilderDialogInteraction(
                'disable-signature',
                LinkHandler::getInstance()->getControllerLink(UserDisableSignatureAction::class, ['id' => '%s']),
                'wcf.user.disableSignature',
                static fn(User $user) => $user->disableSignature === 0
                    && $user->canDisableSignature()
            ),
            new RpcInteraction(
                'enable-signature',
                'core/users/%s/enable-signature',
                'wcf.user.enableSignature',
                isAvailableCallback: static fn(User $user) => $user->disableSignature === 1
                    && $user->canDisableSignature()
            ),
            new LinkInteraction(
                'send-mail',
                UserMailForm::class,
                'wcf.acp.user.action.sendMail',
                static fn(User $user) => $user->canMail()
            ),
            new RpcInteraction(
                'send-new-password',
                'core/users/%s/send-new-password',
                'wcf.acp.user.action.sendNewPassword',
                InteractionConfirmationType::Custom,
                'wcf.acp.user.action.sendNewPassword.confirmMessage',
                static fn(User $user) => $user->canSendNewPassword()
            ),
            new RpcInteraction(
                'disconnect-third-party-login',
                'core/users/%s/disconnect-third-party-login',
                'wcf.acp.user.action.disconnectThirdPartyLogin',
                InteractionConfirmationType::Custom,
                static fn(User $user) => WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.user.action.disconnectThirdPartyLogin.confirmMessage',
                    ['user' => $user]
                ),
                static fn(User $user) => $user->canDisconnectThirdPartyLogin(),
                InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'disable-multifactor',
                'core/users/%s/disable-multifactor',
                'wcf.acp.user.action.disableMultifactor',
                InteractionConfirmationType::Custom,
                static fn(User $user) => WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.user.action.disableMultifactor.confirmMessage',
                    ['user' => $user]
                ),
                static fn(User $user) => $user->canDisableMultifactor()
            ),
            new LinkInteraction(
                'export-gdpr',
                UserExportGdprAction::class,
                'wcf.acp.user.exportGdpr',
                static fn(User $user) => $user->canExportGdpr()
            ),
            new Divider(),
            new DeleteInteraction(
                'core/users/%s',
                static fn(User $user) => $user->canDelete()
            ),
            new UserContentRemoveInteraction(),
        ]);

        EventHandler::getInstance()->fire(
            new UserInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return User::class;
    }
}
