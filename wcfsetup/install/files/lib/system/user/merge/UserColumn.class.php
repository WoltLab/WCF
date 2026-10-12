<?php

namespace wcf\system\user\merge;

/**
 * Represents a database column referencing a user that is reassigned to the
 * destination user when users are merged.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserColumn
{
    /**
     * @param string $tableName name of the database table
     * @param string $userIDColumn name of the column containing the user id
     * @param ?string $usernameColumn name of the column containing the username, which is updated alongside the user id
     * @param bool $ignore uses `UPDATE IGNORE` to skip rows that would violate a unique key
     * @param ?string $excludedColumn rows whose value in this column matches the destination user are skipped, e.g. to prevent the destination user from following themselves
     */
    public function __construct(
        public readonly string $tableName,
        public readonly string $userIDColumn = 'userID',
        public readonly ?string $usernameColumn = null,
        public readonly bool $ignore = false,
        public readonly ?string $excludedColumn = null,
    ) {}
}
