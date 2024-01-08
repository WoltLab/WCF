<?php

namespace wcf\system\user\command;

use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowEditor;
use wcf\data\user\User;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\UserFollowUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Saves that a user is following another user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class Follow
{
    public function __construct(private readonly User $user, private readonly User $target)
    {
    }

    public function __invoke(): void
    {
        $follow = UserFollowEditor::createOrIgnore([
            'userID' => $this->user->userID,
            'followUserID' => $this->target->userID,
            'time' => TIME_NOW,
        ]);

        if ($follow === null) {
            return;
        }

        \assert($follow instanceof UserFollow);
        $this->sendNotification($follow);
        $this->fireActivityEvent();
        $this->resetUserStorage();
    }

    private function sendNotification(UserFollow $follow): void
    {
        UserNotificationHandler::getInstance()->fireEvent(
            'following',
            'com.woltlab.wcf.user.follow',
            new UserFollowUserNotificationObject($follow),
            [$follow->followUserID]
        );
    }

    private function fireActivityEvent(): void
    {
        UserActivityEventHandler::getInstance()->fireEvent(
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
