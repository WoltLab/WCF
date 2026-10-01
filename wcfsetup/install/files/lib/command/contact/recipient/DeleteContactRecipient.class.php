<?php

namespace wcf\command\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientBuilder;
use wcf\event\contact\recipient\ContactRecipientDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes a contact recipient.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteContactRecipient
{
    public function __construct(
        private readonly ContactRecipient $recipient,
    ) {}

    public function __invoke(): void
    {
        ContactRecipientBuilder::delete($this->recipient);

        EventHandler::getInstance()->fire(new ContactRecipientDeleted($this->recipient));
    }
}
