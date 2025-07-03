<?php

namespace wcf\acp\form;

use wcf\acp\page\ContactRecipientListPage;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\ContactRecipientInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the form to update a contact form recipient.
 *
 * @author  Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ContactRecipientEditForm extends ContactRecipientAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.contact.recipients';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_CONTACT_FORM'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.contact.canManageContactForm'];

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }

        $this->formObject = new ContactRecipient(\intval($_REQUEST['id']));
        if (!$this->formObject->recipientID) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new ContactRecipientInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(ContactRecipientListPage::class)
            ),
        ]);
    }
}
