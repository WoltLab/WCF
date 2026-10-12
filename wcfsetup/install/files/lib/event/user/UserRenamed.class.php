<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\event\IPsr14Event;
use wcf\system\user\rename\UsernameColumn;

/**
 * Indicates that a user has been renamed.
 *
 * Listeners should update the username stored alongside the user id in their
 * own database tables. Columns registered through `updateUsernameColumn()`
 * are updated after all listeners have been executed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserRenamed implements IPsr14Event
{
    /**
     * @var list<UsernameColumn>
     */
    private array $usernameColumns = [];

    public function __construct(
        public readonly User $user,
        public readonly string $oldUsername,
    ) {}

    /**
     * Registers a column containing the username that is replaced with the
     * new username of the user.
     *
     * @see UsernameColumn::__construct()
     */
    public function updateUsernameColumn(
        string $tableName,
        string $userIDColumn = 'userID',
        string $usernameColumn = 'username',
    ): void {
        $this->usernameColumns[] = new UsernameColumn(
            $tableName,
            $userIDColumn,
            $usernameColumn,
        );
    }

    /**
     * @return list<UsernameColumn>
     */
    public function getUsernameColumns(): array
    {
        return $this->usernameColumns;
    }
}
