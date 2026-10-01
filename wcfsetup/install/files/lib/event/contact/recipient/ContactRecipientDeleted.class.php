<?php

namespace wcf\event\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\event\IPsr14Event;

/**
 * Indicates that a contact recipient has been deleted.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ContactRecipientDeleted implements IPsr14Event
{
    public function __construct(
        public readonly ContactRecipient $recipient,
    ) {}
}
