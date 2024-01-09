<?php

namespace wcf\system\user\command;

use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowEditor;
use wcf\data\user\User;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Saves that a user is unfollowing another user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class Unfollow
{
    public function __construct(private readonly User $user, private readonly User $target)
    {
    }

    public function __invoke(): void
    {
        $follow = UserFollow::getFollow($this->user->userID, $this->target->userID);

        if ($follow->followID) {
            $followEditor = new UserFollowEditor($follow);
            $followEditor->delete();

            $this->removeActivityEvent();
        }

        $this->resetUserStorage();
    }

    private function removeActivityEvent(): void
    {
        UserActivityEventHandler::getInstance()->removeEvent(
            'com.woltlab.wcf.user.recentActivityEvent.follow',
            $this->target->userID
        );
    }

    private function resetUserStorage(): void
    {
        UserStorageHandler::getInstance()->reset([$this->target->userID], 'followerUserIDs');
        UserStorageHandler::getInstance()->reset([$this->user->userID], 'followingUserIDs');
    }
}
