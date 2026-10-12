<?php

namespace wcf\acp\form;

use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\IFormNode;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\form\option\IFormOption;
use wcf\system\form\option\SharedConfigurationFormFields;

/**
 * Provides the option type and configuration form fields shared by forms that
 * add custom options based on the form option system.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TFormOptionAddForm
{
    /**
     * Returns whether the given value of a configuration form field is considered
     * to be set and therefore has to be stored. Values that represent the default
     * state of a form field, for example an empty text or a disabled checkbox, are
     * not stored.
     */
    private function isConfigurationValueSet(?IFormNode $formField, mixed $value): bool
    {
        // `BooleanFormField::getSaveValue()` returns `0` or `1` instead of a
        // boolean, therefore a disabled checkbox has to be recognized explicitly.
        if ($formField instanceof BooleanFormField && $value === 0) {
            return false;
        }

        return $value !== null
            && $value !== ''
            && $value !== false
            && $value !== [];
    }

    /**
     * @return string[]
     */
    protected function getConfigurationFormFieldIds(): array
    {
        $ids = [];

        foreach ($this->getAvailableFormOptions() as $option) {
            foreach ($option->getConfigurationFormFields() as $formFieldId) {
                $ids[] = $formFieldId;
            }
        }

        return \array_unique($ids);
    }

    /**
     * @return list<IFormField>
     */
    protected function getSharedConfigurationFormFields(): array
    {
        $sharedConfigurationFormFields = new SharedConfigurationFormFields();
        $matrix = [];

        foreach ($this->getAvailableFormOptions() as $option) {
            foreach ($option->getConfigurationFormFields() as $formFieldId) {
                if (!isset($matrix[$formFieldId])) {
                    $matrix[$formFieldId] = [];
                }

                $matrix[$formFieldId][] = $option->getId();
            }
        }

        $formFields = [];

        foreach ($matrix as $formFieldId => $dependencies) {
            $formField = $sharedConfigurationFormFields->getFormField($formFieldId);
            \assert($formField !== null);
            $formField->addDependency(
                ValueFormFieldDependency::create($formFieldId . 'OptionTypeDependency')
                    ->fieldId('optionType')
                    ->values($dependencies)
            );
            $formFields[] = $formField;
        }

        return $formFields;
    }

    protected function getOptionTypeFormField(): SelectFormField
    {
        return SelectFormField::create('optionType')
            ->label('wcf.form.option.optionType')
            ->immutable($this->formAction !== 'create')
            ->options(FormOptionHandler::getInstance()->getSortedOptionTypes($this->getSelectableFormOptions()))
            ->required();
    }

    /**
     * Returns the form options that are supported as the type of an option,
     * including types that are not selectable for new options.
     *
     * @return array<string, IFormOption>
     * @since 6.3
     */
    protected function getAvailableFormOptions(): array
    {
        return FormOptionHandler::getInstance()->getOptions();
    }

    /**
     * Returns the form options that can be selected as the type of a new option.
     *
     * @return array<string, IFormOption>
     * @since 6.3
     */
    protected function getSelectableFormOptions(): array
    {
        return $this->getAvailableFormOptions();
    }
}
