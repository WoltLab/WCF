<?php

namespace wcf\system\view\user\profile;

use wcf\acp\form\UserEditForm;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\event\user\profile\UserProfileHeaderManagementOptionCollecting;
use wcf\event\user\profile\UserProfileHeaderSearchContentLinkCollecting;
use wcf\event\user\profile\UserProfileStatItemCollecting;
use wcf\page\MembersListPage;
use wcf\system\event\EventHandler;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\interaction\user\UserProfileInteractions;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents the view for the user profile header.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserProfileHeaderView
{
    /**
     * @var UserProfileStatItem[]
     */
    private array $statItems = [];

    /**
     * @var UserProfileHeaderViewSearchContentLink[]
     */
    private array $searchContentLinks = [];

    /**
     * @var UserProfileHeaderViewManagementOption[]
     */
    private array $managementOptions = [];

    private StandaloneInteractionContextMenuComponent $interactionContextMenu;

    public function __construct(
        public readonly UserProfile $user,
    ) {
        $this->initStatItems();
        $this->initSearchContentLinks();
        $this->initInteractionContextMenu();
        $this->initManagementOptions();
    }

    public function __toString(): string
    {
        return WCF::getTPL()->render('wcf', 'userProfileHeader', [
            'view' => $this,
        ]);
    }

    public function hasStatItems(): bool
    {
        return $this->statItems !== [];
    }

    /**
     * @return UserProfileStatItem[]
     */
    public function getStatItems(): array
    {
        return $this->statItems;
    }

    public function hasSearchContentLinks(): bool
    {
        return $this->searchContentLinks !== [];
    }

    /**
     * @return UserProfileHeaderViewSearchContentLink[]
     */
    public function getSearchContentLinks(): array
    {
        return $this->searchContentLinks;
    }

    public function getInteractionContextMenu(): StandaloneInteractionContextMenuComponent
    {
        return $this->interactionContextMenu;
    }

    public function hasManagementOptions(): bool
    {
        return $this->managementOptions !== [];
    }

    /**
     * @return UserProfileHeaderViewManagementOption[]
     */
    public function getManagementOptions(): array
    {
        return $this->managementOptions;
    }

    public function canEditUser(): bool
    {
        return $this->user->canEdit() || (WCF::getUser()->userID == $this->user->userID && $this->user->canEditOwnProfile());
    }

    public function canEditCoverPhoto(): bool
    {
        return $this->user->canEdit() || (WCF::getUser()->userID == $this->user->userID && (WCF::getSession()->getPermission('user.profile.coverPhoto.canUploadCoverPhoto') || $this->user->coverPhotoHash));
    }

    public function canAddCoverPhoto(): bool
    {
        return $this->user->canEdit() || (WCF::getUser()->userID == $this->user->userID && WCF::getSession()->getPermission('user.profile.coverPhoto.canUploadCoverPhoto'));
    }

    public function isInAccessibleGroup(): bool
    {
        return UserGroup::isAccessibleGroup($this->user->getGroupIDs());
    }

    private function initStatItems(): void
    {
        $event = new UserProfileStatItemCollecting($this->user);
        EventHandler::getInstance()->fire($event);
        if ($event->getItems() !== []) {
            $this->statItems = \array_merge($this->statItems, $event->getItems());
        }
    }

    private function initSearchContentLinks(): void
    {
        $event = new UserProfileHeaderSearchContentLinkCollecting($this->user);
        EventHandler::getInstance()->fire($event);
        $this->searchContentLinks = $event->getLinks();
    }

    private function initInteractionContextMenu(): void
    {
        $this->interactionContextMenu = new StandaloneInteractionContextMenuComponent(
            new UserProfileInteractions(),
            $this->user,
            LinkHandler::getInstance()->getControllerLink(MembersListPage::class),
            cssClassName: 'userProfileHeader__button'
        );
    }

    private function initManagementOptions(): void
    {
        if (!$this->isInAccessibleGroup() || $this->user->userID == WCF::getUser()->userID) {
            return;
        }

        if (WCF::getSession()->getPermission('admin.user.canBanUser')) {
            $this->managementOptions[] = UserProfileHeaderViewManagementOption::forButton(
                WCF::getLanguage()->get($this->user->banned ? 'wcf.user.unban' : 'wcf.user.ban'),
                'class="jsButtonUserBan"',
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
            $this->managementOptions[] = UserProfileHeaderViewManagementOption::forButton(
                WCF::getLanguage()->get($this->user->disableAvatar ? 'wcf.user.enableAvatar' : 'wcf.user.disableAvatar'),
                'class="jsButtonUserDisableAvatar"',
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
            $this->managementOptions[] = UserProfileHeaderViewManagementOption::forButton(
                WCF::getLanguage()->get($this->user->disableSignature ? 'wcf.user.enableSignature' : 'wcf.user.disableSignature'),
                'class="jsButtonUserDisableSignature"',
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto')) {
            $this->managementOptions[] = UserProfileHeaderViewManagementOption::forButton(
                WCF::getLanguage()->get($this->user->disableCoverPhoto ? 'wcf.user.enableCoverPhoto' : 'wcf.user.disableCoverPhoto'),
                'class="jsButtonUserDisableCoverPhoto"',
            );
        }
        if (WCF::getSession()->getPermission('admin.user.canEnableUser')) {
            $this->managementOptions[] = UserProfileHeaderViewManagementOption::forButton(
                WCF::getLanguage()->get($this->user->pendingActivation() ? 'wcf.acp.user.enable' : 'wcf.acp.user.disable'),
                'class="jsButtonUserEnable"',
            );
        }
        if (WCF::getSession()->getPermission('admin.general.canUseAcp') && WCF::getSession()->getPermission('admin.user.canEditUser')) {
            $this->managementOptions[] = UserProfileHeaderViewManagementOption::forLink(
                WCF::getLanguage()->get('wcf.user.edit'),
                LinkHandler::getInstance()->getControllerLink(UserEditForm::class, ['id' => $this->user->userID]),
            );
        }

        $event = new UserProfileHeaderManagementOptionCollecting($this->user);
        EventHandler::getInstance()->fire($event);
        if ($event->getOptions() !== []) {
            $this->managementOptions = \array_merge($this->managementOptions, $event->getOptions());
        }
    }
}
