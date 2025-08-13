<?php

namespace wcf\command\user;

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
        private readonly int $userID,
    ) {}

    public function __invoke(): void
    {
        $this->removeUserIgnore(WCF::getUser()->userID, $this->userID);

        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID, $this->userID], 'ignoredUserIDs');

        $event = new UserUnignored($this->userID);
        EventHandler::getInstance()->fire($event);
    }

    private function removeUserIgnore(int $userID, int $ignoreUserID): void
    {
        $sql = "DELETE FROM wcf1_user_ignore
                WHERE       userID = ?
                        AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$userID, $ignoreUserID]);
    }
}
