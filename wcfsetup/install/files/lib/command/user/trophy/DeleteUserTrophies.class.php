<?php

namespace wcf\command\user\trophy;

use wcf\command\user\UpdateUser;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyBuilder;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\user\trophy\UserTrophiesDeleted;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Revokes trophies from users.
 *
 * Updates the trophy points and removes special trophies the users no longer
 * own.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteUserTrophies
{
    /**
     * @param UserTrophy[] $userTrophies
     */
    public function __construct(
        private readonly array $userTrophies,
    ) {}

    public function __invoke(): void
    {
        if ($this->userTrophies === []) {
            return;
        }

        $userTrophyIDs = $userIDs = $trophyPointChanges = [];
        foreach ($this->userTrophies as $userTrophy) {
            $userTrophyIDs[] = $userTrophy->userTrophyID;
            $userIDs[] = $userTrophy->userID;

            // Disabled trophies do not count towards the trophy points.
            if (!$userTrophy->getTrophy()->isDisabled()) {
                $trophyPointChanges[$userTrophy->userID] ??= 0;
                $trophyPointChanges[$userTrophy->userID]--;
            }
        }

        UserTrophyBuilder::deleteAll($userTrophyIDs);

        $this->removeSpecialTrophies(\array_values(\array_unique($userIDs)));

        foreach ($trophyPointChanges as $userID => $count) {
            new UpdateUser(
                UserBuilder::forUpdate(new User($userID))->incrementTrophyPoints($count)
            )();
        }

        EventHandler::getInstance()->fire(new UserTrophiesDeleted($this->userTrophies));
    }

    /**
     * Removes the special trophies of the given users that they no longer own.
     *
     * @param non-empty-list<int> $userIDs
     */
    private function removeSpecialTrophies(array $userIDs): void
    {
        foreach (UserTrophyList::getUserTrophies($userIDs) as $userID => $userTrophies) {
            $trophyIDs = \array_map(static fn(UserTrophy $userTrophy) => $userTrophy->trophyID, $userTrophies);

            $conditionBuilder = new PreparedStatementConditionBuilder();
            if ($trophyIDs !== []) {
                $conditionBuilder->add('trophyID NOT IN (?)', [\array_values(\array_unique($trophyIDs))]);
            }
            $conditionBuilder->add('userID = ?', [$userID]);

            $sql = "DELETE FROM wcf1_user_special_trophy
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
        }

        UserStorageHandler::getInstance()->reset($userIDs, 'specialTrophies');
    }
}
