<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\event\user\UserUnignored;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Saves that a user is no longer ignoring another user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UnignoreUser
{
    public function __construct(
        private readonly User $user,
        private readonly User $target,
    ) {}

    public function __invoke(): void
    {
        $this->removeUserIgnore($this->user, $this->target);

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'ignoredUserIDs');
        UserStorageHandler::getInstance()->reset([$this->target->userID], 'ignoredByUserIDs');

        $event = new UserUnignored($this->user, $this->target);
        EventHandler::getInstance()->fire($event);
    }

    private function removeUserIgnore(User $user, User $target): void
    {
        $sql = "DELETE FROM wcf1_user_ignore
                WHERE       userID = ?
                        AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$user->userID, $target->userID]);
    }
}
