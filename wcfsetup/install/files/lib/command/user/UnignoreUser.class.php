<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\event\user\UserUnignored;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * The current user unignores the given user.
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
        private readonly User $unignoreUser,
    ) {}

    public function __invoke(): void
    {
        $this->removeUserIgnore($this->user, $this->unignoreUser);

        UserStorageHandler::getInstance()->reset([$this->user->userID, $this->unignoreUser->userID], 'ignoredUserIDs');

        $event = new UserUnignored($this->user, $this->unignoreUser);
        EventHandler::getInstance()->fire($event);
    }

    private function removeUserIgnore(User $user, User $unignoreUser): void
    {
        $sql = "DELETE FROM wcf1_user_ignore
                WHERE       userID = ?
                        AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$user->userID, $unignoreUser->userID]);
    }
}
