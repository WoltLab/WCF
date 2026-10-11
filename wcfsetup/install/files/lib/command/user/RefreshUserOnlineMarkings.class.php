<?php

namespace wcf\command\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Assigns the user group with the highest priority as the online marking of the
 * given users. Missing default user groups are added and the user groups that
 * do not match the activation state are removed beforehand.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class RefreshUserOnlineMarkings
{
    /**
     * @param list<User> $users
     */
    public function __construct(
        private readonly array $users,
    ) {}

    public function __invoke(): void
    {
        $addToGroupIDs = $removeFromGroupIDs = $newGroupIDs = $userToGroup = [];
        foreach ($this->users as $user) {
            $groupIDs = $user->getGroupIDs();

            if ($user->pendingActivation()) {
                $requiredGroupIDs = [UserGroup::EVERYONE, UserGroup::GUESTS];
                $invalidGroupID = UserGroup::USERS;
            } else {
                $requiredGroupIDs = [UserGroup::EVERYONE, UserGroup::USERS];
                $invalidGroupID = UserGroup::GUESTS;
            }

            $missingGroupIDs = \array_values(\array_diff($requiredGroupIDs, $groupIDs));
            if ($missingGroupIDs !== []) {
                $addToGroupIDs[$user->userID] = $missingGroupIDs;
                $groupIDs = \array_merge($groupIDs, $missingGroupIDs);
            }

            if (\in_array($invalidGroupID, $groupIDs, true)) {
                $removeFromGroupIDs[$user->userID] = $invalidGroupID;
                $groupIDs = \array_values(\array_diff($groupIDs, [$invalidGroupID]));
            }

            if (isset($addToGroupIDs[$user->userID]) || isset($removeFromGroupIDs[$user->userID])) {
                $newGroupIDs[$user->userID] = $groupIDs;
            }

            $groupID = $this->getOnlineGroupID($groupIDs);
            if ($groupID !== $user->userOnlineGroupID) {
                $userToGroup[$user->userID] = $groupID;
            }
        }

        if ($addToGroupIDs !== []) {
            $sql = "INSERT INTO wcf1_user_to_group
                                (userID, groupID)
                    VALUES      (?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($addToGroupIDs as $userID => $groupIDs) {
                foreach ($groupIDs as $groupID) {
                    $statement->execute([$userID, $groupID]);
                }
            }
            WCF::getDB()->commitTransaction();
        }

        if ($removeFromGroupIDs !== []) {
            $sql = "DELETE FROM wcf1_user_to_group
                    WHERE       userID = ?
                            AND groupID = ?";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($removeFromGroupIDs as $userID => $groupID) {
                $statement->execute([$userID, $groupID]);
            }
            WCF::getDB()->commitTransaction();
        }

        foreach ($newGroupIDs as $userID => $groupIDs) {
            UserStorageHandler::getInstance()->update($userID, 'groupIDs', \serialize($groupIDs));
        }

        if ($userToGroup !== []) {
            $sql = "UPDATE  wcf1_user
                    SET     userOnlineGroupID = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($userToGroup as $userID => $groupID) {
                $statement->execute([
                    $groupID,
                    $userID,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }
    }

    /**
     * @param int[] $groupIDs
     */
    private function getOnlineGroupID(array $groupIDs): int
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('groupID IN (?)', [$groupIDs]);

        $sql = "SELECT      groupID
                FROM        wcf1_user_group
                " . $conditionBuilder . "
                ORDER BY    priority DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute($conditionBuilder->getParameters());

        return (int)$statement->fetchSingleColumn();
    }
}
