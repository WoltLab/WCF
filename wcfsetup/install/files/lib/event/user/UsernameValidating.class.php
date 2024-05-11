<?php

namespace wcf\event\user;

use wcf\event\IInterruptableEvent;
use wcf\event\TInterruptableEvent;

/**
 * Indicates that a username is currently validated. If this event
 * is interrupted, the username is considered to be invalid.
 *
 * This event will not be fired for usernames changed by an administrator.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class UsernameValidating extends \wcf\system\user\event\UsernameValidating implements IInterruptableEvent
{
    use TInterruptableEvent;

    public function __construct(
        public readonly string $username
    ) {
    }
}
