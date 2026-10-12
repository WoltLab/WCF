<?php

namespace wcf\system\user\rename;

/**
 * Represents a database column containing the username that is stored
 * alongside the user id and updated when a user is renamed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UsernameColumn
{
    /**
     * @param string $tableName name of the database table
     * @param string $userIDColumn name of the column containing the user id
     * @param string $usernameColumn name of the column containing the username
     */
    public function __construct(
        public readonly string $tableName,
        public readonly string $userIDColumn = 'userID',
        public readonly string $usernameColumn = 'username',
    ) {}
}
