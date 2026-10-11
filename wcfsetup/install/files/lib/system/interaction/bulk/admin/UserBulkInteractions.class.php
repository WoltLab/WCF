<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\acp\action\UserContentRemoveAction;
use wcf\acp\form\UserAssignToGroupForm;
use wcf\acp\form\UserContentRevertChangesForm;
use wcf\acp\form\UserEmailAddressExportForm;
use wcf\acp\form\UserMailForm;
use wcf\acp\form\UserMergeForm;
use wcf\action\UserBanAction;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\interaction\bulk\admin\UserBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\interaction\bulk\BulkFormBuilderDialogInteraction;
use wcf\system\interaction\bulk\BulkLinkInteraction;
use wcf\system\interaction\bulk\BulkRpcInteraction;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Bulk interaction provider for the user administration.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new BulkRpcInteraction(
                'enable',
                'core/users/%s/enable',
                'wcf.acp.user.enable',
                isAvailableCallback: static fn(User $user) => $user->pendingActivation()
                    && $user->canEnable()
            ),
            new BulkRpcInteraction(
                'confirm-email',
                'core/users/%s/confirm-email',
                'wcf.acp.user.action.confirmEmail',
                isAvailableCallback: static fn(User $user) => !$user->isEmailConfirmed()
                    && $user->canEnable()
            ),
            new BulkRpcInteraction(
                'unconfirm-email',
                'core/users/%s/unconfirm-email',
                'wcf.acp.user.action.unconfirmEmail',
                isAvailableCallback: static fn(User $user) => $user->isEmailConfirmed()
                    && $user->canEnable()
            ),
            new BulkRpcInteraction(
                'resend-activation-mail',
                'core/users/%s/resend-activation-mail',
                'wcf.acp.user.action.resendActivationMail',
                isAvailableCallback: static fn(User $user) => $user->canResendActivationMail()
            ),
            new BulkFormBuilderDialogInteraction(
                'ban',
                UserBanAction::class,
                'wcf.acp.user.ban',
                static fn(User $user) => $user->banned === 0
                    && $user->canBan()
            ),
            new BulkRpcInteraction(
                'unban',
                'core/users/%s/unban',
                'wcf.acp.user.unban',
                isAvailableCallback: static fn(User $user) => $user->banned === 1
                    && $user->canBan()
            ),
            new BulkLinkInteraction(
                'assign-to-group',
                UserAssignToGroupForm::class,
                'wcf.acp.user.action.assignToGroup',
                static fn(User $user) => $user->canEdit()
            ),
            new BulkLinkInteraction(
                'merge',
                UserMergeForm::class,
                'wcf.acp.user.action.merge',
                static fn(User $user) => $user->canEdit()
            ),
            new BulkLinkInteraction(
                'send-mail',
                UserMailForm::class,
                'wcf.acp.user.action.sendMail',
                static fn(User $user) => $user->canMail()
            ),
            new BulkLinkInteraction(
                'export-mail-address',
                UserEmailAddressExportForm::class,
                'wcf.acp.user.action.exportMailAddress',
                static fn() => WCF::getSession()->hasPermission('admin.user.canEditMailAddress')
            ),
            new BulkRpcInteraction(
                'send-new-password',
                'core/users/%s/send-new-password',
                'wcf.acp.user.action.sendNewPassword',
                InteractionConfirmationType::Custom,
                'wcf.acp.user.action.sendNewPassword.confirmMessage.bulk',
                static fn(User $user) => $user->canSendNewPassword()
            ),
            new BulkLinkInteraction(
                'revert-content-changes',
                UserContentRevertChangesForm::class,
                'wcf.acp.user.action.revertContentChanges',
                static fn() => \MODULE_EDIT_HISTORY !== 0
                    && WCF::getSession()->hasPermission('admin.content.canBulkRevertContentChanges')
            ),
            new BulkDeleteInteraction(
                'core/users/%s',
                static fn(User $user) => $user->canDelete()
            ),
            new class(
                'remove-content',
                UserContentRemoveAction::class,
                'wcf.acp.content.removeContent',
                static fn(User $user) => $user->canDelete()
            ) extends BulkFormBuilderDialogInteraction {
                #[\Override]
                public function renderInitialization(string $containerId): string
                {
                    $identifier = StringUtil::encodeJS($this->getIdentifier());
                    $containerId = StringUtil::encodeJS($containerId);

                    return <<<HTML
                        <script data-relocate="true">
                            require(['WoltLabSuite/Core/Acp/Component/User/RemoveContent'], ({ setupBulk }) => {
                                setupBulk('{$identifier}', document.getElementById('{$containerId}'));
                            });
                        </script>
                        HTML;
                }
            },
        ]);

        EventHandler::getInstance()->fire(
            new UserBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return UserList::class;
    }
}
