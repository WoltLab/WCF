<?php

namespace wcf\event\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a contact recipient has been created.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ContactRecipientCreated implements IPsr14Event
{
    public function __construct(
        public readonly ContactRecipient $recipient,
        public readonly ContactRecipientBuilder $builder,
    ) {}
}
