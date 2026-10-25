<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Marks the email address of a user as confirmed. Enables a disabled user as
 * well unless the registration requires an activation by an administrator.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ConfirmUserEmail
{
    public function __construct(
        private readonly User $user,
        private readonly bool $sendNotification = true,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setEmailConfirmed(null);
        $user = new UpdateUser($builder)();

        if (
            $user->pendingActivation()
            && ((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN) === 0
        ) {
            $user = new EnableUser($user, $this->sendNotification)();
        }

        return $user;
    }
}
