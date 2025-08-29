<?php

namespace wcf\command\user\profile;

use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Tracks a visit to a user profile, updating the time if the visitor is already
 * known.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class TrackUserProfileVisitor
{
    public function __construct(
        private readonly User $user,
        private readonly User $target,
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
            $this->target->userID,
            $this->user->userID,
            $this->time,
        ]);
    }
}
