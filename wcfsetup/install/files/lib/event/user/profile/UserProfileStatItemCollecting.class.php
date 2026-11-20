<?php

namespace wcf\event\user\profile;

use wcf\data\user\UserProfile;
use wcf\event\IPsr14Event;
use wcf\system\view\user\profile\UserProfileStatItem;
use wcf\system\view\user\profile\UserStatContext;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Collects the statistic items of a user, which are displayed in the user profile header,
 * the user card and the sidebar of messages.
 *
 * Use `$context` to check where the items are displayed, e.g. to respect options
 * that only apply to the sidebar of messages.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserProfileStatItemCollecting implements IPsr14Event
{
    /**
     * @var UserProfileStatItem[]
     */
    private array $items = [];

    /**
     * @since 6.3 The `$context` parameter was added.
     */
    public function __construct(
        public readonly UserProfile $user,
        public readonly UserStatContext $context = UserStatContext::ProfileHeader,
    ) {
        $this->registerDefaultItems();
    }

    public function register(UserProfileStatItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return UserProfileStatItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Returns true if the items are displayed in the sidebar of messages.
     *
     * @since 6.3
     */
    public function isMessageSidebar(): bool
    {
        return $this->context === UserStatContext::MessageSidebar;
    }

    private function registerDefaultItems(): void
    {
        if (
            \MODULE_LIKE !== 0
            && $this->user->likesReceived !== 0
            && (!$this->isMessageSidebar() || \MESSAGE_SIDEBAR_ENABLE_LIKES_RECEIVED !== 0)
        ) {
            $this->register(UserProfileStatItem::forLink(
                WCF::getLanguage()->get('wcf.user.reactionsReceived'),
                StringUtil::formatNumeric($this->user->likesReceived),
                $this->user->getLink() . '#likes'
            ));
        }

        if (
            $this->user->activityPoints !== 0
            && (!$this->isMessageSidebar() || \MESSAGE_SIDEBAR_ENABLE_ACTIVITY_POINTS !== 0)
        ) {
            $this->register(UserProfileStatItem::forButton(
                WCF::getLanguage()->get('wcf.user.activityPoint'),
                StringUtil::formatNumeric($this->user->activityPoints),
                'activityPointsDisplay',
                'data-user-id="' . $this->user->userID . '"'
            ));
        }

        if (
            $this->context !== UserStatContext::ProfileHeader
            && (!$this->isMessageSidebar() || \MESSAGE_SIDEBAR_ENABLE_TROPHY_POINTS !== 0)
            && $this->user->showTrophyPoints()
        ) {
            $this->register(UserProfileStatItem::forButton(
                WCF::getLanguage()->get('wcf.user.trophy.trophyPoints'),
                StringUtil::formatNumeric($this->user->trophyPoints),
                'userTrophyOverlayList',
                'data-user-id="' . $this->user->userID . '"'
            ));
        }
    }
}
