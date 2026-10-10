<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

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
     * format of the value of native `datetime-local` inputs
     * @since 6.3
     */
    const NATIVE_TIME_FORMAT = 'Y-m-d\TH:i';

    #[\Override]
    public function getHtmlVariables()
    {
        return [
            'dateRangeFormFieldFromValue' => $this->getInputValue($this->getFromValue()),
            'dateRangeFormFieldToValue' => $this->getInputValue($this->getToValue()),
        ];
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
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            $this->value = [
                'from' => \is_string($value['from'] ?? null) ? $this->readInputValue($value['from']) : '',
                'to' => \is_string($value['to'] ?? null) ? $this->readInputValue($value['to']) : '',
            ];
        }

        return $this;
    }

    /**
     * Returns the given value in the format of the native date input.
     *
     * @since 6.3
     */
    protected function getInputValue(string $value): string
    {
        if ($value === '' || !$this->supportsTime()) {
            return $value;
        }

        $dateTime = \DateTimeImmutable::createFromFormat(static::TIME_FORMAT, $value);
        if ($dateTime === false) {
            return '';
        }

        return $dateTime->setTimezone($this->getInputTimeZone())->format(static::NATIVE_TIME_FORMAT);
    }

    /**
     * Converts the value submitted by the native date input into the internal
     * format. Native `datetime-local` inputs submit the value without a time
     * zone offset in the time zone of the input, optionally including the seconds.
     *
     * @since 6.3
     */
    protected function readInputValue(string $value): string
    {
        if ($value === '' || !$this->supportsTime()) {
            return $value;
        }

        foreach (['!' . static::NATIVE_TIME_FORMAT, '!Y-m-d\TH:i:s'] as $format) {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $value, $this->getInputTimeZone());
            if ($dateTime !== false) {
                return $dateTime->format(static::TIME_FORMAT);
            }
        }

        return $value;
    }

    /**
     * Returns the time zone in which the native date time inputs display and
     * submit their values.
     *
     * @since 6.3
     */
    protected function getInputTimeZone(): \DateTimeZone
    {
        if (
            $this->hasFieldAttribute('data-ignore-timezone')
            && $this->getFieldAttribute('data-ignore-timezone') === 'true'
        ) {
            return new \DateTimeZone('UTC');
        }

        return WCF::getUser()->getTimeZone();
    }

    /**
     * Sets if not only the date, but also the time can be set.
     */
    public function supportTime(bool $supportsTime = true): static
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

    #[\Override]
    public function validate()
    {
        if ($this->isRequired() && ($this->getFromValue() === '' || $this->getToValue() === '')) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        if ($this->getFromValue() !== '') {
            $dateTime = \DateTime::createFromFormat(
                $this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT,
                $this->getFromValue()
            );
            if ($dateTime === false) {
                $this->addValidationError(new FormFieldValidationError('invalid'));
            }
        }

        if ($this->getToValue() !== '') {
            $dateTime = \DateTime::createFromFormat(
                $this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT,
                $this->getToValue()
            );
            if ($dateTime === false) {
                $this->addValidationError(new FormFieldValidationError('invalid'));
            }
        }
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
