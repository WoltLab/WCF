<?php

namespace wcf\acp\form;

use wcf\command\contact\recipient\CreateContactRecipient;
use wcf\command\contact\recipient\UpdateContactRecipient;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientBuilder;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\data\DatabaseObjectBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new contact form recipient.
 *
 * @author  Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<ContactRecipient, ContactRecipientBuilder>
 */
class ContactRecipientAddForm extends AbstractDatabaseObjectBuilderForm
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
    public string $objectEditLinkController = ContactRecipientEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): ContactRecipientBuilder
    {
        if ($this->formObject !== null) {
            return ContactRecipientBuilder::forUpdate($this->formObject);
        }

        return ContactRecipientBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateContactRecipient($builder);
        }

        return new CreateContactRecipient($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $isAdministratorRecipient = $this->formObject !== null && $this->formObject->isAdministrator !== 0;

        $emailFormField = EmailFormField::create('email')
            ->label('wcf.user.email')
            ->immutable($isAdministratorRecipient)
            ->required()
            ->saveValueCallback(static function (ContactRecipientBuilder $builder, EmailFormField $field): void {
                // The administrator recipient always uses `MAIL_ADMIN_ADDRESS`.
                if ($field->isImmutable()) {
                    return;
                }

                $builder->setEmail($field->getL10nValues());
            })
            ->loadValueCallback(static function (ContactRecipient $object, IFormField $field): void {
                if ($object->isAdministrator !== 0) {
                    $field->value($object->getEmail());
                } else {
                    $field->value($object->getL10nValues('email'));
                }
            });

        if (!$isAdministratorRecipient) {
            $emailFormField->l10n();
        }

        $this->form->appendChildren([
            TextFormField::create('name')
                ->label('wcf.acp.contact.recipient.name')
                ->l10n()
                ->required()
                ->maximumLength(255)
                ->saveValueCallback(static function (ContactRecipientBuilder $builder, TextFormField $field): void {
                    $builder->setName($field->getL10nValues());
                })
                ->loadValueCallback(static function (ContactRecipient $object, IFormField $field): void {
                    $field->value($object->getL10nValues('name'));
                }),
            $emailFormField,
            ShowOrderFormField::create()
                ->options($this->getContactRecipient(), labelLanguageItems: false)
                ->saveValueCallback(static function (ContactRecipientBuilder $builder, ShowOrderFormField $field): void {
                    $showOrder = $field->getSaveValue();
                    if ($showOrder !== null) {
                        $builder->setShowOrder($showOrder);
                    }
                })
                ->loadValueCallback(static function (ContactRecipient $object, IFormField $field): void {
                    $field->value($object->showOrder);
                }),
            BooleanFormField::create('isDisabled')
                ->label('wcf.acp.contact.recipient.isDisabled')
                ->saveValueCallback(static function (ContactRecipientBuilder $builder, BooleanFormField $field): void {
                    $builder->setIsDisabled((bool)$field->getSaveValue());
                })
                ->loadValueCallback(static function (ContactRecipient $object, IFormField $field): void {
                    $field->value($object->isDisabled);
                }),
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

        // The labels of selection options are printed as HTML.
        return \array_map(
            static fn(ContactRecipient $recipient) => StringUtil::encodeHTML($recipient->getName()),
            $recipientList->getObjects()
        );
    }
}
