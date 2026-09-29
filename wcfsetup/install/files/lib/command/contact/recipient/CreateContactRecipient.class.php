<?php

namespace wcf\command\contact\recipient;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientBuilder;
use wcf\event\contact\recipient\ContactRecipientCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a contact recipient.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateContactRecipient
{
    public function __construct(
        private readonly ContactRecipientBuilder $builder,
    ) {}

    public function __invoke(): ContactRecipient
    {
        $recipient = $this->builder->create();

        EventHandler::getInstance()->fire(new ContactRecipientCreated(
            $recipient,
            $this->builder
        ));

        return $recipient;
    }
}
