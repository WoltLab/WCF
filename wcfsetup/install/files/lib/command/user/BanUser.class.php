<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Bans a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class BanUser
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        /** timestamp at which the ban expires, `0` bans the user permanently */
        private readonly int $expires = 0,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setBanned(true)
            ->setBanReason($this->reason)
            ->setBanExpires(\min($this->expires, 2147483647));

        return (new UpdateUser($builder))();
    }
}
