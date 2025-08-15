<?php

namespace wcf\command\user\profile;

use wcf\system\WCF;

/**
 * Registers a user profile visitor or updates the time of an existing one.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class RegisterUserProfileVisitor
{
    public function __construct(
        private readonly int $ownerID,
        private readonly int $userID,
        private readonly int $time
    ) {}

    public function __invoke(): void
    {
        $sql = "INSERT INTO             wcf1_user_profile_visitor
                                        (ownerID, userID, time)
                VALUES                  (?, ?, ?)
                ON DUPLICATE KEY UPDATE time = VALUES(time)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->ownerID,
            $this->userID,
            $this->time,
        ]);
    }
}
