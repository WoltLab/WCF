<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for a numeric range.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class NumericRangeFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    IMaximumFormField,
    IMinimumFormField,
    INullableFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TMaximumFormField;
    use TMinimumFormField;
    use TNullableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/NumericRange';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_numericRangeFormField';

    /**
     * Is `true` if only integer values are supported.
     */
    protected bool $integerValues = false;

    public function __construct()
    {
        $this->addFieldClass('short');
    }

    /**
     * @return ?string
     */
    #[\Override]
    public function getSaveValue()
    {
        if ($this->getFromValue() === '' && $this->getToValue() === '' && $this->isNullable()) {
            return null;
        }

        return $this->getFromValue() . ';' . $this->getToValue();
    }

    #[\Override]
    public function readValue()
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    #[\Override]
    public function validate()
    {
        if ($this->isRequired() && ($this->getFromValue() === '' || $this->getToValue() === '')) {
            $this->addValidationError(new FormFieldValidationError('empty'));

            return;
        }

        foreach ([$this->getFromValue(), $this->getToValue()] as $value) {
            if ($value === '') {
                continue;
            }

            $error = $this->validateBound($value);
            if ($error !== null) {
                $this->addValidationError($error);

                return;
            }
        }
    }

    /**
     * Returns the validation error for the given lower or upper bound or `null`
     * if the bound is a valid number within the minimum and maximum.
     */
    private function validateBound(string $value): ?FormFieldValidationError
    {
        $isValid = $this->integerValues ? \preg_match('~^-?\d+$~', $value) === 1 : \is_numeric($value);
        if (!$isValid) {
            return new FormFieldValidationError(
                'invalid',
                'wcf.form.field.numeric.error.invalid'
            );
        }

        if ($this->getMinimum() !== null && $value < $this->getMinimum()) {
            return new FormFieldValidationError(
                'minimum',
                'wcf.form.field.numeric.error.minimum',
                ['minimum' => $this->getMinimum()]
            );
        }

        if ($this->getMaximum() !== null && $value > $this->getMaximum()) {
            return new FormFieldValidationError(
                'maximum',
                'wcf.form.field.numeric.error.maximum',
                ['maximum' => $this->getMaximum()]
            );
        }

        return null;
    }

    #[\Override]
    public function value(mixed $value)
    {
        $values = \explode(';', $value);
        if (\count($values) !== 2) {
            throw new \InvalidArgumentException(
                "Given value does not match format for field '{$this->getId()}'."
            );
        }

        $this->value = [
            'from' => $this->integerValues ? \intval($values[0]) : \floatval($values[0]),
            'to' => $this->integerValues ? \intval($values[1]) : \floatval($values[1]),
        ];

        return $this;
    }

    public function getFromValue(): string
    {
        return $this->value['from'] ?? '';
    }

    public function getToValue(): string
    {
        return $this->value['to'] ?? '';
    }

    /**
     * Defines if this form field allows only integer values.
     */
    public function integerValues(bool $value = true): static
    {
        $this->integerValues = $value;

        return $this;
    }

    /**
     * Returns the default value for the input element's step attribute.
     */
    public function getDefaultStep(): int|string
    {
        if ($this->integerValues) {
            return 1;
        } else {
            return 'any';
        }
    }
}
