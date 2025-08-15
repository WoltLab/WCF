<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user's special trophies have been updated.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserSpecialTrophiesUpdated implements IPsr14Event
{
    public function __construct(
        public readonly User $user,
        /** @var int[] */
        public readonly array $trophyIDs
    ) {}
}
