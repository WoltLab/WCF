<?php

namespace wcf\event\user\profile;

use wcf\data\user\UserProfile;
use wcf\event\IPsr14Event;
use wcf\system\view\user\profile\UserProfileStatItem;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Collects the statistic items for the user profile.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserProfileStatItemCollecting implements IPsr14Event
{
    /**
     * @var UserProfileStatItem[]
     */
    private array $items = [];

    public function __construct(public readonly UserProfile $user)
    {
        if (\MODULE_LIKE !== 0 && $this->user->likesReceived !== 0) {
            $this->register(UserProfileStatItem::forLink(
                WCF::getLanguage()->get('wcf.user.reactionsReceived'),
                StringUtil::formatNumeric($this->user->likesReceived),
                '#likes'
            ));
        }

        if ($this->user->activityPoints !== 0) {
            $this->register(UserProfileStatItem::forButton(
                WCF::getLanguage()->get('wcf.user.activityPoint'),
                StringUtil::formatNumeric($this->user->activityPoints),
                'activityPointsDisplay',
                'data-user-id="' . $this->user->userID . '"'
            ));
        }
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
}
