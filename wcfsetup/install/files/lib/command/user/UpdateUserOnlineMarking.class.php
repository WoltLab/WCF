<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\event\user\UserOnlineMarkingUpdated;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Updates the online marking group ID of a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UpdateUserOnlineMarking
{
    public function __construct(
        private readonly User $user
    ) {}

    public function __invoke(): void
    {
        $groupIDs = (new UpdateUserGroups($this->user))();

        $newOnlineGroupID = $this->newOnlineGroupID($groupIDs);

        $this->updateOnlineGroupID($newOnlineGroupID);

        $event = new UserOnlineMarkingUpdated($this->user, $newOnlineGroupID);
        EventHandler::getInstance()->fire($event);
    }

    /**
     * @param list<int> $groupIDs
     */
    private function newOnlineGroupID(array $groupIDs): ?int
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('groupID IN (?)', [$groupIDs]);

        $sql = "SELECT   groupID
                FROM     wcf1_user_group
                " . $conditionBuilder . "
                ORDER BY priority DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn() ?: null;
    }

    private function updateOnlineGroupID(?int $newOnlineGroupID): void
    {
        $sql = "UPDATE wcf1_user SET userOnlineGroupID = ? WHERE userID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $statement->execute([$newOnlineGroupID, $this->user->userID]);
    }
}
