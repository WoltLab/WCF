<?php

namespace wcf\event\message;

use wcf\data\user\User;
use wcf\event\IInterruptableEvent;
use wcf\event\TInterruptableEvent;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Indicates that a new message by a user is currently validated. If this event is interrupted,
 * the message is considered to be spam.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class MessageSpamChecking implements IInterruptableEvent
{
    use TInterruptableEvent;

    public function __construct(
        public readonly HtmlInputProcessor $processor,
        public readonly ?User $user = null,
        public readonly string $ipAddress = '',
        public readonly string $subject = '',
    ) {
    }
}
