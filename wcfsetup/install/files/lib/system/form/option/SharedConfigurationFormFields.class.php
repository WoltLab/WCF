<?php

namespace wcf\system\form\option;

use wcf\event\form\option\SharedConfigurationFormFieldCollecting;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\FloatFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SelectOptionsFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * Provides the available shared configuration form fields.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SharedConfigurationFormFields
{
    /**
     * @var array<string, IFormField>
     */
    private array $formFields;

    public function __construct()
    {
        $this->formFields = \array_merge($this->getDefaultFormFields(), $this->getEventFormFields());
    }

    /**
     * @return array<string, IFormField>
     */
    private function getDefaultFormFields(): array
    {
        return [
            'currency' => TextFormField::create('currency')
                ->label('wcf.form.option.shared.currency')
                ->value('EUR')
                ->addFieldClass('short')
                ->required(),
            'defaultTextValue' => TextFormField::create('defaultTextValue')
                ->label('wcf.form.option.shared.defaultValue')
                ->addFieldClass('medium'),
            'maxLength' => IntegerFormField::create('maxLength')
                ->label('wcf.form.option.shared.maxLength'),
            'minIntegerValue' => IntegerFormField::create('minIntegerValue')
                ->label('wcf.form.option.shared.minValue'),
            'maxIntegerValue' => IntegerFormField::create('maxIntegerValue')
                ->label('wcf.form.option.shared.maxValue'),
            'minFloatValue' => FloatFormField::create('minFloatValue')
                ->label('wcf.form.option.shared.minValue'),
            'maxFloatValue' => FloatFormField::create('maxFloatValue')
                ->label('wcf.form.option.shared.maxValue'),
            'selectOptions' => SelectOptionsFormField::create('selectOptions')
                ->label('wcf.form.option.shared.selectOptions')
                ->required(),
            'required' => BooleanFormField::create('required')
                ->label('wcf.form.option.shared.required')
                ->value(false),
            'unit' => TextFormField::create('unit')
                ->label('wcf.form.option.shared.unit')
                ->addFieldClass('short')
        ];
    }

    /**
     * @return array<string, IFormField>
     */
    private function getEventFormFields(): array
    {
        $event = new SharedConfigurationFormFieldCollecting();
        EventHandler::getInstance()->fire($event);

        return $event->getFormFields();
    }

    /**
     * @return array<string, IFormField>
     */
    public function getFormFields(): array
    {
        return $this->formFields;
    }

    public function getFormField(string $identifier): ?IFormField
    {
        return $this->formFields[$identifier] ?? null;
    }
}
