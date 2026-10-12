<?php

namespace wcf\acp\form;

use wcf\data\user\option\UserOption;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\option\user\TUserOptionFormFields;
use wcf\system\WCF;

/**
 * Provides functions to set the default values of user options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<null>
 */
class UserOptionSetDefaultsForm extends AbstractFormBuilderForm
{
    use TUserOptionFormFields {
        getUserOptionFormField as getDefaultUserOptionFormField;
    }

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.userOptionDefaults';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * editable options of the settings grouped by the name of their category
     * @var array<string, UserOption[]>
     */
    protected array $options = [];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->readOptions();
    }

    /**
     * Reads the options whose default values can be set.
     */
    protected function readOptions(): void
    {
        $this->options = $this->getUserOptionsByCategory(
            'settings',
            static fn(UserOption $option) => $option->validateOptions()
                && $option->validatePermissions()
                && $option->isEditable()
        );
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChild(
            FormContainer::create('general')
                ->appendChild(
                    BooleanFormField::create('applyChangesToExistingUsers')
                        ->label('wcf.acp.user.option.setDefaults.applyChangesToExistingUsers')
                        ->description('wcf.acp.user.option.setDefaults.applyChangesToExistingUsers.description')
                )
        );

        $this->form->appendChildren($this->getUserOptionFormContainers($this->options));
    }

    /**
     * Returns the form field for the given user option, a default value is
     * never required.
     */
    protected function getUserOptionFormField(UserOption $option): AbstractFormField
    {
        return $this->getDefaultUserOptionFormField($option)->required(false);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if ($_POST === [] || $this->form->showsSuccessMessage()) {
            $this->loadDefaultValues();
        }
    }

    /**
     * Sets the current default values of the options as the values of the form fields.
     */
    protected function loadDefaultValues(): void
    {
        foreach ($this->getOptions() as $option) {
            $value = $option->defaultValue;
            if ($value === null || $value === '0000-00-00') {
                continue;
            }

            $this->form->getFormField($option->getIdentifier())
                ?->value($option->getFormOption()->unserializeValue($value));
        }
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $defaultValues = [];
        foreach ($this->getOptions() as $option) {
            $formField = $this->form->getFormField($option->getIdentifier());
            if ($formField === null) {
                continue;
            }

            $value = $formField->getSaveValue();
            $defaultValues[$option->optionID] = [
                'option' => $option,
                'value' => $value !== null ? $option->getFormOption()->serializeValue($value) : null,
            ];
        }

        if ($this->form->getFormField('applyChangesToExistingUsers')?->getSaveValue() === true) {
            $this->applyChangesToExistingUsers($defaultValues);
        }

        $sql = "UPDATE  wcf1_user_option
                SET     defaultValue = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($defaultValues as $optionID => ['value' => $value]) {
            $statement->execute([$value, $optionID]);
        }

        UserOptionCacheBuilder::getInstance()->reset();

        // The form is rebuilt after saving, which requires the updated options.
        $this->readOptions();
        $this->saved();
    }

    /**
     * Sets the changed default values as the values of all existing users.
     * Options without a default value are skipped.
     *
     * @param array<int, array{option: UserOption, value: ?string}> $defaultValues
     */
    protected function applyChangesToExistingUsers(array $defaultValues): void
    {
        $columns = $values = [];
        foreach ($defaultValues as ['option' => $option, 'value' => $value]) {
            if ($value === null || $value === $option->defaultValue) {
                continue;
            }

            $columns[] = $option->getIdentifier() . ' = ?';
            $values[] = $value;
        }

        if ($columns === []) {
            return;
        }

        $sql = "UPDATE  wcf1_user_option_value
                SET     " . \implode(', ', $columns);
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($values);
    }

    /**
     * @return UserOption[]
     */
    protected function getOptions(): array
    {
        return \array_merge(...\array_values($this->options));
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'hasOptions' => $this->options !== [],
        ]);
    }
}
