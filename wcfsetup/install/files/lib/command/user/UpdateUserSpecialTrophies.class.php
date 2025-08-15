<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\event\user\UserSpecialTrophiesUpdated;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Updates the special trophies of a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UpdateUserSpecialTrophies
{
    public function __construct(
        private readonly User $user,
        /** @var int[] */
        private readonly array $trophyIDs
    ) {
    }

    public function __invoke(): void
    {
        $this->deleteExistingSpecialTrophies();
        $this->insertSpecialTrophies();

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'specialTrophies');

        $event = new UserSpecialTrophiesUpdated($this->user, $this->trophyIDs);
        EventHandler::getInstance()->fire($event);
    }

    private function deleteExistingSpecialTrophies(): void
    {
        $sql = "DELETE FROM wcf1_user_special_trophy
                WHERE       userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->user->userID]);
    }

    private function insertSpecialTrophies(): void
    {
        if ($this->trophyIDs === []) {
            return;
        }

        $sql = "INSERT INTO wcf1_user_special_trophy
                            (userID, trophyID)
                VALUES      (?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($this->trophyIDs as $trophyID) {
            $statement->execute([
                $this->user->userID,
                $trophyID,
            ]);
        }
    }
}
