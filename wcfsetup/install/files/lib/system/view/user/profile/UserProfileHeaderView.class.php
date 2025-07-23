<?php

namespace wcf\system\view\user\profile;

use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\event\user\profile\UserProfileHeaderSearchContentLinkCollecting;
use wcf\event\user\profile\UserProfileStatItemCollecting;
use wcf\page\MembersListPage;
use wcf\system\event\EventHandler;
use wcf\system\interaction\InteractionContextMenuComponentConfiguration;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\interaction\user\UserManagementInteractions;
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

    private StandaloneInteractionContextMenuComponent $interactionContextMenu;
    private StandaloneInteractionContextMenuComponent $managementContextMenu;

    public function __construct(
        public readonly UserProfile $user,
    ) {
        $this->initStatItems();
        $this->initSearchContentLinks();
        $this->initInteractionContextMenu();
        $this->initManagementContextMenu();
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

    public function getManagementContextMenu(): StandaloneInteractionContextMenuComponent
    {
        return $this->managementContextMenu;
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
            configuration: new InteractionContextMenuComponentConfiguration(
                cssClassName: 'userProfileHeader__button',
                buttonCssClassName: 'button small'
            )
        );
    }

    private function initManagementContextMenu(): void
    {
        $this->managementContextMenu = new StandaloneInteractionContextMenuComponent(
            new UserManagementInteractions(),
            $this->user,
            LinkHandler::getInstance()->getControllerLink(MembersListPage::class),
            configuration: new InteractionContextMenuComponentConfiguration(
                cssClassName: 'userProfileHeader__button',
                buttonCssClassName: 'button small',
                dropdownMenuCssClassName: 'userProfileHeader__managementOptions',
                icon: 'gear',
                tooltip: 'wcf.user.profile.management'
            )
        );

        if (!$this->isInAccessibleGroup() || $this->user->userID == WCF::getUser()->userID) {
            return;
        }
    }
}
