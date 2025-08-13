<?php

namespace wcf\command\trophy;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyEditor;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\trophy\TrophyDisabled;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Disables the given trophy.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableTrophy
{
    public function __construct(private readonly Trophy $trophy) {}

    public function __invoke(): void
    {
        (new TrophyEditor($this->trophy))->update([
            'isDisabled' => 1,
        ]);

        $this->deleteSpecialTrophies($this->trophy->trophyID);
        $this->updateTrophyPoints($this->trophy->trophyID);
        $this->resetUserStorage();

        $event = new TrophyDisabled($this->trophy);
        EventHandler::getInstance()->fire($event);
    }

    private function updateTrophyPoints(int $trophyID): void
    {
        $sql = "SELECT   COUNT(*) as count, userID
                FROM     wcf1_user_trophy
                WHERE    trophyID = ?
                GROUP BY userID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$trophyID]);

        foreach ($statement->fetchMap('userID', 'count') as $userID => $count) {
            (new UserEditor(new User(null, ['userID' => $userID])))->updateCounters([
                'trophyPoints' => $count * -1,
            ]);
        }
    }

    private function deleteSpecialTrophies(int $trophyID): void
    {
        $sql = "DELETE FROM wcf1_user_special_trophy
                WHERE       trophyID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $trophyID,
        ]);
    }

    private function resetUserStorage(): void
    {
        UserStorageHandler::getInstance()->resetAll('specialTrophies');
    }
}
