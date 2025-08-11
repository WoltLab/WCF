<?php

namespace wcf\event\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\event\IPsr14Event;

/**
 * Indicates that a contact recipient has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ContactRecipientEnabled implements IPsr14Event
{
    public function __construct(public readonly ContactRecipient $recipient) {}
}
