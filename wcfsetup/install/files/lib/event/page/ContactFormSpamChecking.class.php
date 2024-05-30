<?php

namespace wcf\event\page;

use wcf\event\IInterruptableEvent;
use wcf\event\TInterruptableEvent;

/**
 * Indicates that a new contact form message is currently validated. If this event is interrupted,
 * the message is considered to be spam.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ContactFormSpamChecking implements IInterruptableEvent
{
    use TInterruptableEvent;

    public function __construct(
        public readonly string $email,
        public readonly string $ipAddress,
    ) {
    }
}
