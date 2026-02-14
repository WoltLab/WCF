<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for selecting a single value.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class SelectFormField extends AbstractFormField implements
    ICssClassFormField,
    IImmutableFormField,
    ISelectionFormField
{
    use TCssClassFormField;
    use TImmutableFormField;
    use TSelectionFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_selectFormField';

    /**
     * @since 6.2
     */
    private bool $ignoreInvalidValues = false;

    /**
     * @since 6.2
     */
    private ?string $defaultValue = null;

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        if ($value !== null && $value !== '') {
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
     *
     * @since 6.2
     */
    public function ignoreInvalidValues(bool $ignoreInvalidValues = true): self
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
     *
     * @since 6.2
     */
    public function defaultValue(?string $defaultValue = null): self
    {
        if ($defaultValue !== null && !isset($this->getOptions()[$defaultValue])) {
            throw new \InvalidArgumentException("Unknown default value '{$defaultValue}' for field '{$this->getId()}'.");
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @since 6.2
     */
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
