<?php

namespace wcf\command\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientBuilder;
use wcf\event\contact\recipient\ContactRecipientEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a contact recipient.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableContactRecipient {
    public function __construct(private readonly ContactRecipient $recipient) {}

    public function __invoke(): void
    {
        ContactRecipientBuilder::forUpdate($this->recipient)
            ->setIsDisabled(false)
            ->update();

        $event = new ContactRecipientEnabled($this->recipient);
        EventHandler::getInstance()->fire($event);
    }
}
