<?php

namespace wcf\system\view\user\profile;

use wcf\acp\page\UserListPage;
use wcf\data\user\avatar\AvatarDecorator;
use wcf\data\user\avatar\StaticAvatar;
use wcf\data\user\cover\photo\IUserCoverPhoto;
use wcf\data\user\UserProfile;
use wcf\event\user\profile\UserProfileHeaderSearchContentLinkCollecting;
use wcf\event\user\profile\UserProfileStatItemCollecting;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\interaction\admin\UserInteractions;
use wcf\system\interaction\InteractionContextMenuComponentConfiguration;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;

/**
 * Represents the view for the user profile header in the admin panel.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AdminUserProfileHeaderView implements \Stringable
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

    public function __construct(
        public readonly UserProfile $user,
    ) {
        $this->initStatItems();
        $this->initSearchContentLinks();
        $this->initInteractionContextMenu();
    }

    #[\Override]
    public function __toString(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_adminUserProfileHeader', [
            'view' => $this,
        ]);
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

    /**
     * Returns the avatar of the user. Unlike the frontend, a disabled avatar
     * is still shown to the administrator.
     */
    public function getAvatarImageTag(): string
    {
        if ($this->user->disableAvatar !== 0) {
            if ($this->user->avatarPathname !== null) {
                return new AvatarDecorator(new StaticAvatar($this->user->avatarPathname))->getImageTag(128);
            }

            if ($this->user->avatarFileID !== null) {
                $file = FileRuntimeCache::getInstance()->getObject($this->user->avatarFileID);
                if ($file !== null) {
                    return new AvatarDecorator($file)->getImageTag(128);
                }
            }
        }

        return $this->user->getAvatar()->getImageTag(128);
    }

    /**
     * Returns the cover photo of the user. Unlike the frontend, a disabled
     * cover photo is still shown to the administrator.
     */
    public function getCoverPhoto(): IUserCoverPhoto
    {
        return $this->user->getCoverPhoto(true);
    }

    public function getDefaultCoverPhotoUrl(): string
    {
        return StyleHandler::getInstance()->getStyle()->getCoverPhotoUrl();
    }

    public function canEditAvatar(): bool
    {
        return $this->user->canEditAvatar();
    }

    public function canEditCoverPhoto(): bool
    {
        return $this->user->canEditCoverPhoto();
    }

    private function initStatItems(): void
    {
        $event = new UserProfileStatItemCollecting($this->user);
        EventHandler::getInstance()->fire($event);
        $this->statItems = $event->getItems();
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
            new UserInteractions(),
            $this->user->getDecoratedObject(),
            LinkHandler::getInstance()->getControllerLink(UserListPage::class),
            configuration: new InteractionContextMenuComponentConfiguration(
                cssClassName: 'userProfileHeader__button',
                buttonCssClassName: 'button small'
            )
        );
    }
}
