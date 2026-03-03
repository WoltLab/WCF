<?php

namespace wcf\acp\form;

use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientAction;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * Shows the form to create a new contact form recipient.
 *
 * @author  Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<ContactRecipient>
 */
class ContactRecipientAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.contact.recipients.add';

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
    public $objectActionClass = ContactRecipientAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = ContactRecipientEditForm::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $isAdministratorRecipient = $this->formAction === 'edit' && $this->formObject->isAdministrator;

        $emailFormField = EmailFormField::create('email')
            ->label('wcf.user.email')
            ->immutable($isAdministratorRecipient)
            ->required();

        if (!$isAdministratorRecipient) {
            $emailFormField->i18n()
                ->languageItemPattern('wcf.contact.recipient.email\d+');
        }

        $this->form->appendChildren([
            TextFormField::create('name')
                ->label('wcf.acp.contact.recipient.name')
                ->i18n()
                ->languageItemPattern('wcf.contact.recipient.name\d+')
                ->required(),
            $emailFormField,
            ShowOrderFormField::create()
                ->options($this->getContactRecipient()),
            BooleanFormField::create('isDisabled')
                ->label('wcf.acp.contact.recipient.isDisabled'),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function getContactRecipient(): array
    {
        $recipientList = new ContactRecipientList();
        $recipientList->sqlOrderBy = 'showOrder ASC';
        $recipientList->readObjects();

        return \array_map(static fn($recipient) => $recipient->getName(), $recipientList->getObjects());
    }
}
