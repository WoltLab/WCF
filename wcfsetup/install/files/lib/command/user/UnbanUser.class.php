<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Lifts the ban of a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UnbanUser
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setBanned(false)
            ->setBanExpires(0);

        return (new UpdateUser($builder))();
    }
}
