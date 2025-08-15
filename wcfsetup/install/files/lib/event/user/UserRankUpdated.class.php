<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user's rank has been updated.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserRankUpdated implements IPsr14Event
{
    public function __construct(
        public readonly User $user,
        public readonly ?int $newRankID,
    ) {}
}
