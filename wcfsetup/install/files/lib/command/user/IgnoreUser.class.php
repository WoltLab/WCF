<?php

namespace wcf\command\user;

use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\User;
use wcf\event\user\UserIgnored;
use wcf\system\event\EventHandler;
use wcf\system\user\command\Unfollow;
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
        private readonly User $user,
        private readonly User $ignoreUser,
        /** @var UserIgnore::TYPE_BLOCK_DIRECT_CONTACT|UserIgnore::TYPE_HIDE_MESSAGES */
        private readonly int $type
    ) {}

    public function __invoke(): void
    {
        $this->ignoreUser($this->user, $this->ignoreUser, $this->type);

        (new Unfollow(WCF::getUser(), $this->ignoreUser))();

        $this->resetStorage($this->user, $this->ignoreUser);

        $event = new UserIgnored($this->user, $this->ignoreUser, $this->type);
        EventHandler::getInstance()->fire($event);
    }

    private function resetStorage(User $user, User $ignoreUser): void
    {
        UserStorageHandler::getInstance()->reset([$user->userID, $ignoreUser->userID], 'ignoredByUserIDs');
    }

    private function ignoreUser(User $user, User $ignoreUser, int $type): void
    {
        $sql = "INSERT INTO wcf1_user_ignore
                            (userID, ignoreUserID, type, time)
                VALUES      (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE type = VALUES(type), time = VALUES(time)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$user->userID, $ignoreUser->userID, $type, TIME_NOW]);
    }
}
