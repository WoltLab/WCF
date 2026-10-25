<?php

namespace wcf\system\user\group\assignment;

use wcf\command\user\AddUserToGroups;
use wcf\command\user\RemoveUserFromGroups;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\object\filter\builder\UserGroupAssignmentObjectFilterBuilder;
use wcf\system\object\filter\user\UserNotInGroupObjectFilter;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user group assignment-related matters.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupAssignmentHandler extends SingletonFactory
{
    /**
     * list of grouped user group assignment condition object types
     * @var ObjectType[][]
     */
    protected $groupedObjectTypes = [];

    private UserGroupAssignmentObjectFilterBuilder $objectFilterBuilder;

    /**
     * Checks if the users with the given ids should be assigned to new user
     * groups or removed from user groups whose assignments they no longer
     * fulfill.
     *
     * Note: This method uses the user ids as a parameter instead of user objects
     * on purpose to make sure the latest data of the users are fetched.
     *
     * @param int[] $userIDs
     * @return void
     */
    public function checkUsers(array $userIDs)
    {
        if ($userIDs === []) {
            return;
        }

        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();

        /** @var UserGroupAssignment[] $assignments */
        $assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();
        $builder = $this->getObjectFilterBuilder();
        $revocableAssignments = $this->getRevocableAssignments();

        foreach ($userList as $user) {
            $groupIDs = $user->getGroupIDs();
            $newGroupIDs = [];

            foreach ($assignments as $assignment) {
                if (
                    \in_array($assignment->groupID, $groupIDs, true)
                    || \in_array($assignment->groupID, $newGroupIDs, true)
                ) {
                    continue;
                }

                if ($builder->testUser($assignment, $user)) {
                    $newGroupIDs[] = $assignment->groupID;
                }
            }

            if ($newGroupIDs !== []) {
                new AddUserToGroups($user, $newGroupIDs, ignoreUserGroupAssignments: true)();
            }

            $removedGroupIDs = [];
            foreach ($revocableAssignments as $assignment) {
                if (
                    \in_array($assignment->groupID, $groupIDs, true)
                    && !$builder->testUser($assignment, $user)
                ) {
                    $removedGroupIDs[] = $assignment->groupID;
                }
            }

            if ($removedGroupIDs !== []) {
                new RemoveUserFromGroups($user, $removedGroupIDs, ignoreUserGroupAssignments: true)();
            }
        }
    }

    /**
     * Removes up to `$maxRevokes` users from the user groups whose assignment
     * they no longer fulfill and returns the number of removed memberships.
     */
    public function revokeUsers(int $maxRevokes): int
    {
        $i = 0;
        foreach ($this->getRevocableAssignments() as $assignment) {
            $userIDs = $this->getRevocableUserIDs($assignment, $maxRevokes - $i);
            if ($userIDs === []) {
                continue;
            }

            $userList = new UserList();
            $userList->setObjectIDs($userIDs);
            $userList->readObjects();

            foreach ($userList as $user) {
                new RemoveUserFromGroups($user, [$assignment->groupID], ignoreUserGroupAssignments: true)();
            }

            $i += \count($userIDs);
            if ($i >= $maxRevokes) {
                break;
            }
        }

        return $i;
    }

    /**
     * Returns whether members can be automatically removed from the given
     * user group. Built-in user groups are excluded because their membership
     * is managed by the system, groups that grant administrative access are
     * excluded to prevent administrators from locking themselves out.
     */
    public static function isRevocableGroup(UserGroup $group): bool
    {
        return $group->groupType === UserGroup::OTHER
            && !$group->isAdminGroup()
            && !(bool)$group->getGroupOption('admin.general.canUseAcp');
    }

    /**
     * Returns whether the conditions exclude members of the given user group.
     * Such an assignment adds every removed member again, combining it with
     * the automatic revocation would repeatedly remove and add the same users.
     */
    public static function excludesTargetGroup(int $groupID, ?string $conditions): bool
    {
        $values = \json_decode((string)$conditions, true);
        if (!\is_array($values)) {
            return false;
        }

        $notInGroupIdentifier = new UserNotInGroupObjectFilter()->getIdentifier();
        foreach ($values as $value) {
            if (
                \is_array($value)
                && ($value[0] ?? null) === $notInGroupIdentifier
                && (int)($value[1] ?? 0) === $groupID
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the list of grouped user group assignment condition object types.
     *
     * @return  ObjectType[][]
     */
    public function getGroupedObjectTypes()
    {
        return $this->groupedObjectTypes;
    }

    /**
     * Returns the users who fulfill all conditions of the given user group
     * assignment.
     *
     * @return  User[]
     */
    public function getUsers(UserGroupAssignment $assignment, ?int $maxUsers = null)
    {
        $userList = new UserList();
        $userList->getConditionBuilder()->add(
            'user_table.userID NOT IN (
                SELECT  userID
                FROM    wcf1_user_to_group
                WHERE   groupID = ?
            )',
            [$assignment->groupID]
        );
        if ($maxUsers !== null) {
            $userList->sqlLimit = $maxUsers;
        }

        $this->getObjectFilterBuilder()->applyFilters($assignment, $userList);

        $userList->readObjects();

        return $userList->getObjects();
    }

    #[\Override]
    protected function init()
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.userGroupAssignment');
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
     * Returns the assignments whose user groups automatically remove members
     * who no longer fulfill the assignment, indexed by the id of the user group.
     *
     * Automatic revocation is only supported for user groups with a single
     * enabled assignment, any additional assignment for the user group would
     * re-add the removed users.
     *
     * @return array<int, UserGroupAssignment>
     */
    private function getRevocableAssignments(): array
    {
        /** @var UserGroupAssignment[] $assignments */
        $assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();

        $assignmentsByGroupID = [];
        foreach ($assignments as $assignment) {
            $assignmentsByGroupID[$assignment->groupID][] = $assignment;
        }

        $revocableAssignments = [];
        foreach ($assignmentsByGroupID as $groupID => $groupAssignments) {
            if (\count($groupAssignments) !== 1) {
                continue;
            }

            $assignment = $groupAssignments[0];
            if (
                $assignment->revokeAutomatically !== 1
                || self::excludesTargetGroup($groupID, $assignment->conditions)
            ) {
                continue;
            }

            $group = UserGroup::getGroupByID($groupID);
            if ($group === null || !self::isRevocableGroup($group)) {
                continue;
            }

            // An assignment without active filters matches no users, revoking
            // it would remove all members of the user group.
            if (!$this->getObjectFilterBuilder()->applyFilters($assignment, new UserList())) {
                continue;
            }

            $revocableAssignments[$groupID] = $assignment;
        }

        return $revocableAssignments;
    }

    /**
     * Returns the ids of up to `$maxUserIDs` members of the assignment's user
     * group who no longer fulfill the assignment.
     *
     * @return list<int>
     */
    private function getRevocableUserIDs(UserGroupAssignment $assignment, int $maxUserIDs): array
    {
        // The filters are applied to a pseudo list to embed the resulting
        // conditions in a custom query.
        $pseudoUserList = new UserList();
        $this->getObjectFilterBuilder()->applyFilters($assignment, $pseudoUserList);

        $conditionBuilder = $pseudoUserList->getConditionBuilder();
        $conditionBuilder->enableWhereKeyword(false);

        $sql = "SELECT  user_to_group.userID
                FROM    wcf1_user_to_group user_to_group
                WHERE   user_to_group.groupID = ?
                    AND NOT EXISTS (
                        SELECT      1
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value user_option_value
                        ON          user_option_value.userID = user_table.userID
                        {$pseudoUserList->sqlConditionJoins}
                        WHERE       user_table.userID = user_to_group.userID
                                AND ({$conditionBuilder})
                    )";
        $statement = WCF::getDB()->prepare($sql, $maxUserIDs);
        $statement->execute([
            $assignment->groupID,
            ...$conditionBuilder->getParameters(),
        ]);

        return \array_map(static fn($userID) => (int)$userID, $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    private function getObjectFilterBuilder(): UserGroupAssignmentObjectFilterBuilder
    {
        if (!isset($this->objectFilterBuilder)) {
            $this->objectFilterBuilder = new UserGroupAssignmentObjectFilterBuilder();
        }

        return $this->objectFilterBuilder;
    }
}
