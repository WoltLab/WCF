<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for a date range (with a time).
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class DateRangeFormField extends AbstractFormField implements
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
     * is `true` if not only the date, but also the time can be set
     * @var bool
     */
    protected $supportsTime = false;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/DateRange';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_dateRangeFormField';

    const DATE_FORMAT = 'Y-m-d';

    const TIME_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if (!$this->getFromValue() && !$this->getToValue() && $this->isNullable()) {
            return null;
        }

        return $this->getFromValue() . ';' . $this->getToValue();
    }

    /**
     * @inheritDoc
     */
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

    /**
     * Sets if not only the date, but also the time can be set.
     */
    public function supportTime($supportsTime = true): static
    {
        if ($this->value !== null) {
            throw new \BadFunctionCallException(
                "After a value has been set, time support cannot be changed for field '{$this->getId()}'."
            );
        }

        $this->supportsTime = $supportsTime;

        return $this;
    }

    /**
     * Returns `true` if not only the date, but also the time can be set, and
     * returns `false` otherwise.
     *
     * By default, the time cannot be set.
     */
    public function supportsTime(): bool
    {
        return $this->supportsTime;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->isRequired() && (!$this->getFromValue() || !$this->getToValue())) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        if ($this->getFromValue()) {
            $dateTime = \DateTime::createFromFormat(
                $this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT,
                $this->getFromValue()
            );
            if ($dateTime === false) {
                $this->addValidationError(new FormFieldValidationError('invalid'));
            }
        }

        if ($this->getToValue()) {
            $dateTime = \DateTime::createFromFormat(
                $this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT,
                $this->getToValue()
            );
            if ($dateTime === false) {
                $this->addValidationError(new FormFieldValidationError('invalid'));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        $values = \explode(';', $value);
        if (\count($values) !== 2) {
            throw new \InvalidArgumentException(
                "Given value does not match format for field '{$this->getId()}'."
            );
        }

        $this->value = [
            'from' => $values[0],
            'to' => $values[1],
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
}
