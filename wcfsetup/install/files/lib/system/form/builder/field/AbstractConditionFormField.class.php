<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Abstract implementation of a form field that supports conditions.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
abstract class AbstractConditionFormField extends AbstractFormField implements IImmutableFormField
{
    use TImmutableFormField;

    /**
     * @var array<string, string>
     */
    protected array $conditions = [];

    #[\Override]
    public function getSaveValue()
    {
        if ($this instanceof INullableFormField && $this->isNullable() && !$this->getValue()) {
            return;
        }

        if (!\is_array($this->value)) {
            return \serialize([]);
        }

        return \serialize($this->value);
    }

    #[\Override]
    public function validate()
    {
        if ($this->isRequired() && !\is_array($this->value)) {
            $this->addValidationError(new FormFieldValidationError('empty'));

            return;
        }

        if ($this instanceof INullableFormField && $this->value === null) {
            return;
        }

        if (!\array_key_exists($this->getCondition(), $this->conditions)) {
            $this->addValidationError(
                new FormFieldValidationError(
                    'invalidValue',
                    'wcf.global.form.error.noValidSelection'
                )
            );
        }
    }

    #[\Override]
    public function value($value)
    {
        $value = @\unserialize($value);
        if (!\is_array($value)) {
            $value = null;
        } elseif (!\array_key_exists('condition', $value) || !\array_key_exists('value', $value)) {
            throw new \InvalidArgumentException('Invalid serialized value');
        }

        return parent::value($value);
    }

    #[\Override]
    public function getValue()
    {
        return \is_array($this->value) ? $this->value['value'] : null;
    }

    #[\Override]
    final public function readValue()
    {
        if ($this->getDocument()->hasRequestData("{$this->getPrefixedId()}_condition") && $this->hasFieldValue()) {
            $condition = $this->getDocument()->getRequestData("{$this->getPrefixedId()}_condition");

            $this->value = [
                'condition' => $condition,
                'value' => $this->getFieldValue(),
            ];
        }

        return $this;
    }

    /**
     * Sets the conditions for this form field.
     *
     * @param array<string, string> $conditions
     */
    public function conditions(array $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Returns the conditions for this form field.
     *
     * @return array<string, string>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Returns the selected condition for this form field.
     */
    public function getCondition(): string
    {
        return \is_array($this->value) ? $this->value['condition'] : '';
    }

    protected function getFieldValue(): mixed
    {
        return $this->getDocument()->getRequestData($this->getPrefixedId());
    }

    protected function hasFieldValue(): bool
    {
        return $this->getDocument()->hasRequestData($this->getPrefixedId());
    }
}
