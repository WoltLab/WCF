<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Default implementation of a form field for selecting a single value out of
 * a predefined set of options.
 *
 * An empty selection is represented by `null`. Unless the field is required or
 * has a default value, an explicit “No Selection” option is offered.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
abstract class AbstractSingleSelectFormField extends AbstractFormField implements
    ICssClassFormField,
    IImmutableFormField,
    ISelectionFormField
{
    use TCssClassFormField;
    use TImmutableFormField;
    use TSelectionFormField;

    private bool $ignoreInvalidValues = false;

    private string|int|null $defaultValue = null;

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
                $this->value = $value !== '' ? $value : null;
            }
        }

        return $this;
    }

    #[\Override]
    public function validate()
    {
        if ($this->getValue() === null) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } else {
            if (!isset($this->getOptions()[$this->getValue()])) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalidValue',
                    'wcf.global.form.error.noValidSelection'
                ));
            }
        }

        parent::validate();
    }

    #[\Override]
    public function value(mixed $value)
    {
        if ($value === '') {
            $value = null;
        }

        if ($value !== null) {
            if (!isset($this->getOptions()[$value])) {
                if ($this->ignoreInvalidValues) {
                    $value = null;
                } else {
                    throw new \InvalidArgumentException("Unknown value '{$value}' for field '{$this->getId()}'.");
                }
            }
        }

        return parent::value($value);
    }

    /**
     * Ignores invalid values when reading them from data.
     */
    public function ignoreInvalidValues(bool $ignoreInvalidValues = true): static
    {
        $this->ignoreInvalidValues = $ignoreInvalidValues;

        return $this;
    }

    /**
     * Sets an initial default value.
     *
     * The provided value must be among the existing values, setting it to
     * `null` disables this feature. When a default value is present the default
     * option “No Selection” becomes unavailable.
     */
    public function defaultValue(string|int|null $defaultValue = null): static
    {
        if ($defaultValue !== null && !isset($this->getOptions()[$defaultValue])) {
            throw new \InvalidArgumentException("Unknown default value '{$defaultValue}' for field '{$this->getId()}'.");
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function hasDefaultValue(): bool
    {
        return $this->defaultValue !== null;
    }

    #[\Override]
    public function getValue()
    {
        $value = parent::getValue();
        if ($value === null && $this->defaultValue !== null) {
            return $this->defaultValue;
        }

        return $value;
    }
}
