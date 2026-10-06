<?php

namespace wcf\system\interaction\admin;

use wcf\acp\action\UserBanAction;
use wcf\acp\action\UserExportGdprAction;
use wcf\acp\form\UserMailForm;
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
use wcf\system\user\UserAdministrationPolicy;

/**
 * Interaction provider for the user administration.
 *
 * The changes of the state of a user reload the page, which only reloads the
 * affected row of the user list, but keeps the form of the user edit page up
 * to date.
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
                    && UserAdministrationPolicy::canEnable($user),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'disable',
                'core/users/%s/disable',
                'wcf.acp.user.disable',
                isAvailableCallback: static fn(User $user) => !$user->pendingActivation()
                    && UserAdministrationPolicy::canEnable($user),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'confirm-email',
                'core/users/%s/confirm-email',
                'wcf.acp.user.action.confirmEmail',
                isAvailableCallback: static fn(User $user) => !$user->isEmailConfirmed()
                    && UserAdministrationPolicy::canEnable($user),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'unconfirm-email',
                'core/users/%s/unconfirm-email',
                'wcf.acp.user.action.unconfirmEmail',
                isAvailableCallback: static fn(User $user) => $user->isEmailConfirmed()
                    && UserAdministrationPolicy::canEnable($user),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'resend-activation-mail',
                'core/users/%s/resend-activation-mail',
                'wcf.acp.user.action.resendActivationMail',
                isAvailableCallback: static fn(User $user) => UserAdministrationPolicy::canResendActivationMail($user),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new FormBuilderDialogInteraction(
                'ban',
                LinkHandler::getInstance()->getControllerLink(UserBanAction::class, ['id' => '%s']),
                'wcf.acp.user.ban',
                static fn(User $user) => $user->banned === 0
                    && UserAdministrationPolicy::canBan($user),
                InteractionEffect::ReloadPage
            ),
            new RpcInteraction(
                'unban',
                'core/users/%s/unban',
                'wcf.acp.user.unban',
                isAvailableCallback: static fn(User $user) => $user->banned === 1
                    && UserAdministrationPolicy::canBan($user),
                interactionEffect: InteractionEffect::ReloadPage
            ),
            new LinkInteraction(
                'send-mail',
                UserMailForm::class,
                'wcf.acp.user.action.sendMail',
                static fn(User $user) => UserAdministrationPolicy::canMail($user)
            ),
            new RpcInteraction(
                'send-new-password',
                'core/users/%s/send-new-password',
                'wcf.acp.user.action.sendNewPassword',
                InteractionConfirmationType::Custom,
                'wcf.acp.user.action.sendNewPassword.confirmMessage',
                static fn(User $user) => UserAdministrationPolicy::canSendNewPassword($user)
            ),
            new LinkInteraction(
                'export-gdpr',
                UserExportGdprAction::class,
                'wcf.acp.user.exportGdpr',
                static fn(User $user) => UserAdministrationPolicy::canExportGdpr($user)
            ),
            new Divider(),
            new DeleteInteraction(
                'core/users/%s',
                static fn(User $user) => UserAdministrationPolicy::canDelete($user)
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
