<?php

namespace wcf\system\user\event;

use wcf\system\event\IInterruptableEvent;
use wcf\system\event\TInterruptableEvent;

/**
 * Indicates that a username is currently validated. If this event
 * is interrupted, the username is considered to be invalid.
 *
 * This event will not be fired for usernames changed by an administrator.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated use `wcf\event\user\UsernameValidating` instead
 */
class UsernameValidating implements IInterruptableEvent
{
    use TInterruptableEvent;

    public function __construct(
        public readonly string $username
    ) {
    }
}
