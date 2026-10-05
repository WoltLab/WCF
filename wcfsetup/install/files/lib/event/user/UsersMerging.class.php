<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that users are being merged into a destination user.
 *
 * The event is fired before the merged users are deleted, allowing listeners
 * to reassign data from the merged users to the destination user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UsersMerging implements IPsr14Event
{
    /**
     * @param User[] $mergedUsers
     */
    public function __construct(
        public readonly User $destinationUser,
        public readonly array $mergedUsers,
    ) {}
}
