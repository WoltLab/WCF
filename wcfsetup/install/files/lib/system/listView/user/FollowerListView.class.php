<?php

namespace wcf\system\listView\user;

use wcf\data\user\UserProfileList;
use wcf\event\listView\user\FollowerListViewInitialized;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * List view that shows the followers of a specific user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class FollowerListView extends AbstractSimpleUserListView
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
            "user_table.userID IN (SELECT userID FROM wcf1_user_follow WHERE followUserID = ?)",
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
    protected function getInitializedEvent(): FollowerListViewInitialized
    {
        return new FollowerListViewInitialized($this);
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'userID' => $this->userID,
        ];
    }
}
