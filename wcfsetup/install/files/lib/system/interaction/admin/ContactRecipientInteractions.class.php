<?php

namespace wcf\system\interaction\admin;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\event\interaction\admin\ContactRecipientInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for contact recipients.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class ContactRecipientInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_CONTACT_FORM === 0
            || !WCF::getSession()->getPermission('admin.contact.canManageContactForm')
        ) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction('core/contact/recipients/%s', static fn (ContactRecipient $recipient) => !$recipient->originIsSystem),
        ]);

        EventHandler::getInstance()->fire(
            new ContactRecipientInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return ContactRecipient::class;
    }
}
