<?php

namespace wcf\system\interaction\user;

use wcf\acp\form\UserEditForm;
use wcf\action\UserBanAction;
use wcf\data\DatabaseObject;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\event\interaction\user\UserManagementInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\interaction\RpcInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for the management context menu in user profiles.
 * This interaction provider currently only works on the user profile page due to the nature of the underlying (outdated) JavaScript code.
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
                "ban",
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
                "unban",
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

        if (WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
            $this->addInteraction(
                new class('disable-avatar', static fn (UserProfile $user) => UserGroup::isAccessibleGroup($user->getGroupIDs()) && WCF::getUser()->userID !== $user->userID) extends AbstractInteraction {
                    #[\Override]
                    public function render(DatabaseObject $object): string
                    {
                        \assert($object instanceof UserProfile);
                        $title = WCF::getLanguage()->get($object->disableAvatar ? 'wcf.user.enableAvatar' : 'wcf.user.disableAvatar');

                        return <<<HTML
                            <button
                                type="button"
                                class="jsButtonUserDisableAvatar"
                            >{$title}</button>
                            HTML;
                    }
                }
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
            $this->addInteraction(
                new class('disable-signature', static fn (UserProfile $user) => UserGroup::isAccessibleGroup($user->getGroupIDs()) && WCF::getUser()->userID !== $user->userID) extends AbstractInteraction {
                    #[\Override]
                    public function render(DatabaseObject $object): string
                    {
                        \assert($object instanceof UserProfile);
                        $title = WCF::getLanguage()->get($object->disableSignature ? 'wcf.user.enableSignature' : 'wcf.user.disableSignature');

                        return <<<HTML
                            <button
                                type="button"
                                class="jsButtonUserDisableSignature"
                            >{$title}</button>
                            HTML;
                    }
                }
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto')) {
            $this->addInteraction(
                new class('disable-cover-photo', static fn (UserProfile $user) => UserGroup::isAccessibleGroup($user->getGroupIDs()) && WCF::getUser()->userID !== $user->userID) extends AbstractInteraction {
                    #[\Override]
                    public function render(DatabaseObject $object): string
                    {
                        \assert($object instanceof UserProfile);
                        $title = WCF::getLanguage()->get($object->disableCoverPhoto ? 'wcf.user.enableCoverPhoto' : 'wcf.user.disableCoverPhoto');

                        return <<<HTML
                            <button
                                type="button"
                                class="jsButtonUserDisableCoverPhoto"
                            >{$title}</button>
                            HTML;
                    }
                }
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canEnableUser')) {
            $this->addInteraction(
                new class('enable', static fn (UserProfile $user) => UserGroup::isAccessibleGroup($user->getGroupIDs()) && WCF::getUser()->userID !== $user->userID) extends AbstractInteraction {
                    #[\Override]
                    public function render(DatabaseObject $object): string
                    {
                        \assert($object instanceof UserProfile);
                        $title = WCF::getLanguage()->get($object->pendingActivation() ? 'wcf.acp.user.enable' : 'wcf.acp.user.disable');

                        return <<<HTML
                            <button
                                type="button"
                                class="jsButtonUserEnable"
                            >{$title}</button>
                            HTML;
                    }
                }
            );
        }
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
