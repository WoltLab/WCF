<?php

namespace wcf\command\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientBuilder;
use wcf\event\contact\recipient\ContactRecipientUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a contact recipient.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateContactRecipient
{
    public function __construct(
        private readonly ContactRecipientBuilder $builder,
    ) {}

    public function __invoke(): ContactRecipient
    {
        $recipient = $this->builder->update();

        EventHandler::getInstance()->fire(new ContactRecipientUpdated(
            $recipient,
            $this->builder
        ));

        return $recipient;
    }
}
