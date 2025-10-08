<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Send a notification of user registration status.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\user\CreateRegistrationNotification` instead.
 */
final class CreateRegistrationNotification
{
    public function __construct(
        private readonly User $user
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\user\CreateRegistrationNotification(
            $this->user
        ))();
    }
}
