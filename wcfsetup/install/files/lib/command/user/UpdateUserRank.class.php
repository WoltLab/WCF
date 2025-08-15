<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserRankUpdated;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Updates the rank of a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UpdateUserRank
{
    public function __construct(
        private readonly User $user
    ) {}

    public function __invoke(): void
    {
        $newRankID = $this->getNewRankId();

        $this->updateUserRank($this->user, $newRankID);

        $event = new UserRankUpdated($this->user, $newRankID);
        EventHandler::getInstance()->fire($event);
    }

    private function getNewRankId(): ?int
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('user_rank.groupID IN (?)', [$this->user->getGroupIDs()]);
        $conditionBuilder->add('user_rank.requiredPoints <= ?', [$this->user->activityPoints]);

        if ($this->user->gender) {
            $conditionBuilder->add('user_rank.requiredGender IN (?)', [[0, $this->user->gender]]);
        } else {
            $conditionBuilder->add('user_rank.requiredGender = ?', [0]);
        }

        $sql = "SELECT    user_rank.rankID
                FROM      wcf1_user_rank user_rank
                LEFT JOIN wcf1_user_group user_group
                ON        user_group.groupID = user_rank.groupID
                " . $conditionBuilder . "
                ORDER BY  user_group.priority DESC, user_rank.requiredPoints DESC, user_rank.requiredGender DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn() ?: null;
    }

    private function updateUserRank(User $user, ?int $rankID): void
    {
        (new UserEditor($user))->update(['rankID' => $rankID]);
    }
}
