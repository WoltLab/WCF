<?php

namespace wcf\system\trophy\condition;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyList;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\UserList;
use wcf\system\object\filter\builder\TrophyObjectFilterBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles trophy conditions.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class TrophyConditionHandler extends SingletonFactory
{
    /**
     * definition name for trophy conditions
     * @deprecated 6.3 The conditions of trophies are stored as object filters.
     */
    const string CONDITION_DEFINITION_NAME = 'com.woltlab.wcf.condition.trophy';

    /**
     * list of grouped trophy condition object types
     * @var ObjectType[][]
     * @deprecated 6.3 The conditions of trophies are stored as object filters.
     */
    protected array $groupedObjectTypes = [];

    private TrophyObjectFilterBuilder $objectFilterBuilder;

    #[\Override]
    protected function init(): void
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes(self::CONDITION_DEFINITION_NAME);

        foreach ($objectTypes as $objectType) {
            if ($objectType->conditiongroup === null) {
                continue;
            }

            if (!isset($this->groupedObjectTypes[$objectType->conditiongroup])) {
                $this->groupedObjectTypes[$objectType->conditiongroup] = [];
            }

            $this->groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
        }
    }

    /**
     * Returns the list of grouped trophy condition object types.
     *
     * @return ObjectType[][]
     * @deprecated 6.3 The conditions of trophies are stored as object filters.
     */
    public function getGroupedObjectTypes(): array
    {
        return $this->groupedObjectTypes;
    }

    /**
     * Assign trophies based on rules.
     */
    public function assignTrophies(int $maxAssigns = 500): void
    {
        $trophyList = new TrophyList();
        $trophyList->getConditionBuilder()->add('awardAutomatically = ?', [1]);
        $trophyList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $trophyList->readObjects();

        $i = 0;
        foreach ($trophyList as $trophy) {
            $userIDs = $this->getUserIDs($trophy, $maxAssigns - $i);

            foreach ($userIDs as $userID) {
                (new UserTrophyAction([], 'create', [
                    'data' => [
                        'trophyID' => $trophy->trophyID,
                        'userID' => $userID,
                        'time' => \TIME_NOW,
                        'description' => '',
                    ],
                ]))->executeAction();

                if (++$i >= $maxAssigns) {
                    return;
                }
            }
        }
    }

    /**
     * Revoke user trophies which are not longer fulfills the conditions.
     */
    public function revokeTrophies(int $maxRevokes = 500): void
    {
        $trophyList = new TrophyList();
        $trophyList->getConditionBuilder()->add('awardAutomatically = ?', [1]);
        $trophyList->getConditionBuilder()->add('revokeAutomatically = ?', [1]);
        $trophyList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $trophyList->readObjects();

        $i = 0;
        foreach ($trophyList as $trophy) {
            $userTrophyIDs = $this->getRevocableUserTrophyIDs($trophy, $maxRevokes - $i);

            $i += \count($userTrophyIDs);

            if ($userTrophyIDs !== []) {
                (new UserTrophyAction($userTrophyIDs, 'delete'))->executeAction();
            }

            if ($i >= $maxRevokes) {
                return;
            }
        }
    }

    /**
     * Returns the ids of the users who fulfill all conditions of the given
     * trophy and have not received it yet.
     *
     * @return int[]
     */
    private function getUserIDs(Trophy $trophy, int $maxUserIDs): array
    {
        $userList = new UserList();
        // `readObjectIDs()` ignores the join of `UserList::$sqlJoins` that the
        // filters of user options rely on.
        $userList->sqlConditionJoins .= "
            LEFT JOIN   wcf1_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID";
        if (!$this->getObjectFilterBuilder()->applyFilters($trophy, $userList)) {
            return [];
        }

        // prevent multiple awards from a trophy for a user
        $userList->getConditionBuilder()->add(
            'user_table.userID NOT IN (
                SELECT  userID
                FROM    wcf1_user_trophy
                WHERE   trophyID = ?
            )',
            [$trophy->trophyID]
        );
        $userList->sqlLimit = $maxUserIDs;
        $userList->readObjectIDs();

        return $userList->getObjectIDs();
    }

    /**
     * Returns the userTrophyIDs of the users, which no longer fulfills the trophy conditions.
     *
     * @return int[]
     */
    private function getRevocableUserTrophyIDs(Trophy $trophy, int $maxProcessedTrophies): array
    {
        // The filters are applied to a pseudo list to negate the resulting
        // conditions in a custom query.
        $pseudoUserList = new UserList();

        // A trophy without active filters matches no users, negating its
        // conditions would revoke the trophy from all users.
        if (!$this->getObjectFilterBuilder()->applyFilters($trophy, $pseudoUserList)) {
            return [];
        }

        $conditionBuilder = $pseudoUserList->getConditionBuilder();
        $conditionBuilder->enableWhereKeyword(false);

        // The conditions are `NULL` for users without a matching row of a
        // joined table, `IS NOT TRUE` treats those users as not matching.
        $sql = "SELECT      user_trophy.userTrophyID
                FROM        wcf1_user_trophy user_trophy
                INNER JOIN  wcf1_user user_table
                ON          user_table.userID = user_trophy.userID
                LEFT JOIN   wcf1_user_option_value user_option_value
                ON          user_option_value.userID = user_table.userID
                {$pseudoUserList->sqlConditionJoins}
                WHERE       user_trophy.trophyID = ?
                        AND ({$conditionBuilder}) IS NOT TRUE";
        $statement = WCF::getDB()->prepare($sql, $maxProcessedTrophies);
        $statement->execute([
            $trophy->trophyID,
            ...$conditionBuilder->getParameters(),
        ]);

        return \array_map(static fn($userTrophyID) => (int)$userTrophyID, $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    private function getObjectFilterBuilder(): TrophyObjectFilterBuilder
    {
        if (!isset($this->objectFilterBuilder)) {
            $this->objectFilterBuilder = new TrophyObjectFilterBuilder();
        }

        return $this->objectFilterBuilder;
    }
}
