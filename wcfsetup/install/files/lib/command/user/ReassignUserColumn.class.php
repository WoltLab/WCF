<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\merge\UserColumn;
use wcf\system\WCF;

/**
 * Replaces the ids of the merged users in the given column with the id of the
 * destination user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ReassignUserColumn
{
    /**
     * @param int[] $mergedUserIDs
     */
    public function __construct(
        private readonly UserColumn $column,
        private readonly User $destinationUser,
        private readonly array $mergedUserIDs,
    ) {}

    public function __invoke(): void
    {
        if ($this->mergedUserIDs === []) {
            return;
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("{$this->column->userIDColumn} IN (?)", [$this->mergedUserIDs]);
        if ($this->column->excludedColumn !== null) {
            $conditions->add("{$this->column->excludedColumn} <> ?", [$this->destinationUser->userID]);
        }

        $set = "{$this->column->userIDColumn} = ?";
        $parameters = [$this->destinationUser->userID];
        if ($this->column->usernameColumn !== null) {
            $set .= ", {$this->column->usernameColumn} = ?";
            $parameters[] = $this->destinationUser->username;
        }

        $sql = "UPDATE" . ($this->column->ignore ? " IGNORE" : "") . " {$this->column->tableName}
                SET     {$set}
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge($parameters, $conditions->getParameters()));
    }
}
