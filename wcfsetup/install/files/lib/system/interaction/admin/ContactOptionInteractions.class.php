<?php

namespace wcf\system\interaction\admin;

use wcf\data\contact\option\ContactOption;
use wcf\event\interaction\admin\ContactOptionInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for contact options.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class ContactOptionInteractions extends AbstractInteractionProvider
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
            new DeleteInteraction('core/contact/options/%s', static fn (ContactOption $object) => $object->canDelete()),
        ]);

        EventHandler::getInstance()->fire(
            new ContactOptionInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return ContactOption::class;
    }
}
