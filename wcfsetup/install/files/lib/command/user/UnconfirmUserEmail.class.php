<?php

namespace wcf\command\user;

use ParagonIE\ConstantTime\Hex;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Marks the email address of a user as unconfirmed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UnconfirmUserEmail
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setEmailConfirmed(Hex::encode(\random_bytes(20)));

        return new UpdateUser($builder)();
    }
}
