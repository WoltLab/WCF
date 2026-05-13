<?php

namespace wcf\system\listView\user;

use wcf\data\user\UserProfileList;
use wcf\event\listView\user\FollowingListViewInitialized;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * List view that shows who a specific user follows.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class FollowingListView extends AbstractSimpleUserListView
{
    public function __construct(
        public readonly int $userID
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        $list = new UserProfileList();
        $list->getConditionBuilder()->add(
            "user_table.userID IN (SELECT followUserID FROM wcf1_user_follow WHERE userID = ?)",
            [$this->userID]
        );

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        if ($userProfile === null) {
            return false;
        }

        return !$userProfile->isProtected();
    }

    #[\Override]
    protected function getInitializedEvent(): FollowingListViewInitialized
    {
        return new FollowingListViewInitialized($this);
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'userID' => $this->userID,
        ];
    }
}
