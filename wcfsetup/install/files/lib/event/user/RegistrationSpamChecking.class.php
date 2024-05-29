<?php

namespace wcf\event\user;

use wcf\event\IInterruptableEvent;
use wcf\event\TInterruptableEvent;

/**
 * Indicates that a registration by a new user is currently validated. If this event is interrupted,
 * the registration is considered to be a spammer or an undesirable user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RegistrationSpamChecking implements IInterruptableEvent
{
    use TInterruptableEvent;

    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $ipAddress
    ) {
    }
}
