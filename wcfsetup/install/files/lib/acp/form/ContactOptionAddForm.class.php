<?php

namespace wcf\acp\form;

use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionAction;
use wcf\data\contact\option\ContactOptionList;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\option\FormOptionHandler;

/**
 * Shows the contact option add form.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 *
 * @extends AbstractFormOptionAddForm<ContactOption>
 */
class ContactOptionAddForm extends AbstractFormOptionAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.contact.options.add';

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
    public $objectActionClass = ContactOptionAction::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            TextFormField::create('optionTitle')
                ->label('wcf.global.name')
                ->maximumLength(255)
                ->i18n()
                ->languageItemPattern('wcf.contact.option\d+')
                ->required(),
            MultilineTextFormField::create('optionDescription')
                ->label('wcf.global.description')
                ->maximumLength(5000)
                ->rows(5)
                ->i18n()
                ->languageItemPattern('wcf.contact.optionDescription\d+'),
            ShowOrderFormField::create('showOrder')
                ->options($this->getContactOptions()),
            $this->getOptionTypeFormField(),
            ...$this->getSharedConfigurationFormFields(),
            BooleanFormField::create('isDisabled')
                ->label('wcf.acp.customOption.isDisabled'),
        ]);
    }

    #[\Override]
    protected function getOptionTypeFormField(): SelectFormField
    {
        $formField = parent::getOptionTypeFormField();

        // Remove the WYSIWYG type because the contact form does not support
        // embedded objects due to the unique behavior of it.
        $formField->options(
            \array_filter(
                FormOptionHandler::getInstance()->getSortedOptionTypes(),
                static fn($id) => $id !== "wysiwyg",
                \ARRAY_FILTER_USE_KEY,
            ),
        );

        return $formField;
    }

    /**
     * @return array<int, string>
     */
    protected function getContactOptions(): array
    {
        $optionList = new ContactOptionList();
        $optionList->sqlOrderBy = 'showOrder ASC';
        $optionList->readObjects();

        return \array_map(static fn($option) => $option->getTitle(), $optionList->getObjects());
    }
}
