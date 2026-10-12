<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\event\IPsr14Event;
use wcf\system\user\merge\UserColumn;

/**
 * Indicates that users are being merged into a destination user.
 *
 * The event is fired before the merged users are deleted, allowing listeners
 * to reassign data from the merged users to the destination user. Columns
 * registered through `reassignUserColumn()` are updated after all listeners
 * have been executed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UsersMerging implements IPsr14Event
{
    /**
     * @var list<UserColumn>
     */
    private array $userColumns = [];

    /**
     * @param User[] $mergedUsers
     */
    public function __construct(
        public readonly User $destinationUser,
        public readonly array $mergedUsers,
    ) {}

    /**
     * Registers a column whose references to the merged users are replaced
     * with the destination user.
     *
     * @see UserColumn::__construct()
     */
    public function reassignUserColumn(
        string $tableName,
        string $userIDColumn = 'userID',
        ?string $usernameColumn = null,
        bool $ignore = false,
        ?string $excludedColumn = null,
    ): void {
        $this->userColumns[] = new UserColumn(
            $tableName,
            $userIDColumn,
            $usernameColumn,
            $ignore,
            $excludedColumn,
        );
    }

    /**
     * @return list<UserColumn>
     */
    public function getUserColumns(): array
    {
        return $this->userColumns;
    }
}
