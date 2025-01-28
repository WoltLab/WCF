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
    INullableFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
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

    #[\Override]
    public function getSaveValue()
    {
        if (!$this->getFromValue() && !$this->getToValue() && $this->isNullable()) {
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
        if ($this->isRequired() && (!$this->getFromValue() || !$this->getToValue())) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }
    }

    #[\Override]
    public function value($value)
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
