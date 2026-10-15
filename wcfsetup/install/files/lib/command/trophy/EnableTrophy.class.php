<?php

namespace wcf\command\trophy;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\trophy\TrophyEnabled;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Enables a trophy.
 *
 * @author Olaf Braun, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableTrophy
{
    public function __construct(private readonly Trophy $trophy) {}

    public function __invoke(): void
    {
        TrophyBuilder::forUpdate($this->trophy)
            ->setIsDisabled(false)
            ->update();

        TrophyBuilder::resetCache();

        $this->updateTrophyPoints($this->trophy->trophyID);
        $this->resetUserStorage();

        $event = new TrophyEnabled($this->trophy);
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
            UserBuilder::forUpdate(new User(null, ['userID' => $userID]))
                ->incrementTrophyPoints($count)
                ->update();
        }
    }

    private function resetUserStorage(): void
    {
        UserStorageHandler::getInstance()->resetAll('specialTrophies');
    }
}
