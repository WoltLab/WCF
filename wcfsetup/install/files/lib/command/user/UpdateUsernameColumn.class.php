<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\system\user\rename\UsernameColumn;
use wcf\system\WCF;

/**
 * Replaces the username stored in the given column with the current username
 * of the user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateUsernameColumn
{
    public function __construct(
        private readonly UsernameColumn $column,
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        $sql = "UPDATE  {$this->column->tableName}
                SET     {$this->column->usernameColumn} = ?
                WHERE   {$this->column->userIDColumn} = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->user->username,
            $this->user->userID,
        ]);
    }
}
