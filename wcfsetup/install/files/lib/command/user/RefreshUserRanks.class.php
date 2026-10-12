<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Assigns the matching user rank to the given users based on their user
 * groups, activity points and gender.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class RefreshUserRanks
{
    /**
     * @param list<User> $users
     */
    public function __construct(
        private readonly array $users,
    ) {}

    public function __invoke(): void
    {
        $userToRank = [];
        foreach ($this->users as $user) {
            $rankID = $this->getRankID($user);
            if ($rankID !== $user->rankID) {
                $userToRank[$user->userID] = $rankID;
            }
        }

        if ($userToRank === []) {
            return;
        }

        $sql = "UPDATE  wcf1_user
                SET     rankID = ?
                WHERE   userID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($userToRank as $userID => $rankID) {
            $statement->execute([
                $rankID,
                $userID,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }

    private function getRankID(User $user): ?int
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('user_rank.groupID IN (?)', [$user->getGroupIDs()]);
        $conditionBuilder->add('user_rank.requiredPoints <= ?', [$user->activityPoints]);
        if ((int)$user->gender !== 0) {
            $conditionBuilder->add('user_rank.requiredGender IN (?)', [[0, $user->gender]]);
        } else {
            $conditionBuilder->add('user_rank.requiredGender = ?', [0]);
        }

        $sql = "SELECT      user_rank.rankID
                FROM        wcf1_user_rank user_rank
                LEFT JOIN   wcf1_user_group user_group
                ON          user_group.groupID = user_rank.groupID
                " . $conditionBuilder . "
                ORDER BY    user_group.priority DESC, user_rank.requiredPoints DESC, user_rank.requiredGender DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute($conditionBuilder->getParameters());
        $rankID = $statement->fetchSingleColumn();

        return $rankID === false ? null : (int)$rankID;
    }
}
