<?php

namespace wcf\command\user\profile;

use wcf\data\user\profile\visitor\UserProfileVisitor;
use wcf\data\user\profile\visitor\UserProfileVisitorBuilder;
use wcf\data\user\User;

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
        $visitor = UserProfileVisitor::getObject($this->target->userID, $this->user->userID);
        if ($visitor !== null) {
            UserProfileVisitorBuilder::forUpdate($visitor)
                ->setTime($this->time)
                ->update();

            return;
        }

        // A concurrent request may have inserted the visitor in the meantime,
        // in which case the visit has already been tracked.
        UserProfileVisitorBuilder::forCreate()
            ->setOwner($this->target)
            ->setUser($this->user)
            ->setTime($this->time)
            ->createOrIgnore();
    }
}
