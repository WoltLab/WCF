<?php

namespace wcf\command\user;

use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\User;
use wcf\event\user\UserIgnored;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * The current user ignores the given user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class IgnoreUser
{
    public function __construct(
        private readonly int $ignoreUserID,
        /** @var UserIgnore::TYPE_BLOCK_DIRECT_CONTACT|UserIgnore::TYPE_HIDE_MESSAGES */
        private readonly int $type
    ) {}

    public function __invoke(): void
    {
        $user = WCF::getUser();

        $this->ignoreUser($user->userID, $this->ignoreUserID, $this->type);
        $this->deleteFollowing($user->userID, $this->ignoreUserID);

        $this->resetStorage($user, $this->ignoreUserID);

        $event = new UserIgnored($this->ignoreUserID, $this->type);
        EventHandler::getInstance()->fire($event);
    }

    private function resetStorage(User $user, int $ignoreUserID): void
    {
        UserStorageHandler::getInstance()->reset([$user->userID, $ignoreUserID], 'ignoredByUserIDs');
        UserStorageHandler::getInstance()->reset([$user->userID, $ignoreUserID], 'followerUserIDs');
    }

    private function deleteFollowing(int $userID, int $followUserID): void
    {
        $sql = "DELETE FROM wcf1_user_follow
                WHERE       userID = ?
                        AND followUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $userID,
            $followUserID,
        ]);
    }

    private function ignoreUser(int $userID, int $ignoreUserID, int $type): void
    {
        $sql = "INSERT INTO wcf1_user_ignore
                            (userID, ignoreUserID, type, time)
                VALUES      (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE type = VALUES(type), time = VALUES(time)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$userID, $ignoreUserID, $type, TIME_NOW]);
    }
}
