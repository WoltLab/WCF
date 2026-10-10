<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for a time of day without a date.
 *
 * The value is a wall-clock time in `H:i` format that is not subject to any
 * time zone conversion.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class TimeFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    INullableFormField
{
    use TInputAttributeFormField {
        getReservedFieldAttributes as private inputGetReservedFieldAttributes;
    }
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TNullableFormField;
    use TTimeValueFormField;

    /**
     * earliest valid time in `H:i` format or `null` if no earliest valid time has been set
     */
    protected ?string $earliestTime = null;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * latest valid time in `H:i` format or `null` if no latest valid time has been set
     */
    protected ?string $latestTime = null;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_timeFormField';

    const TIME_FORMAT = 'H:i';

    public function __construct()
    {
        $this->addFieldClass('small');
    }

    /**
     * Sets the earliest valid time in `H:i` format and returns this field. If `null`
     * is given, the previously set earliest valid time is unset.
     */
    public function earliestTime(?string $earliestTime = null): static
    {
        if ($earliestTime !== null) {
            $normalizedTime = $this->normalizeTime($earliestTime);
            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(
                    "Earliest time '{$earliestTime}' does not have format '" . static::TIME_FORMAT . "' for field '{$this->getId()}'."
                );
            }

            if ($this->latestTime !== null && $this->latestTime < $normalizedTime) {
                throw new \InvalidArgumentException(
                    "Earliest time '{$normalizedTime}' cannot be later than latest time '{$this->latestTime}' for field '{$this->getId()}'."
                );
            }

            $earliestTime = $normalizedTime;
        }

        $this->earliestTime = $earliestTime;

        return $this;
    }

    /**
     * Returns the earliest valid time in `H:i` format or `null` if no earliest
     * valid time has been set.
     */
    public function getEarliestTime(): ?string
    {
        return $this->earliestTime;
    }

    /**
     * Sets the latest valid time in `H:i` format and returns this field. If `null`
     * is given, the previously set latest valid time is unset.
     */
    public function latestTime(?string $latestTime = null): static
    {
        if ($latestTime !== null) {
            $normalizedTime = $this->normalizeTime($latestTime);
            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(
                    "Latest time '{$latestTime}' does not have format '" . static::TIME_FORMAT . "' for field '{$this->getId()}'."
                );
            }

            if ($this->earliestTime !== null && $normalizedTime < $this->earliestTime) {
                throw new \InvalidArgumentException(
                    "Latest time '{$normalizedTime}' cannot be earlier than earliest time '{$this->earliestTime}' for field '{$this->getId()}'."
                );
            }

            $latestTime = $normalizedTime;
        }

        $this->latestTime = $latestTime;

        return $this;
    }

    /**
     * Returns the latest valid time in `H:i` format or `null` if no latest
     * valid time has been set.
     */
    public function getLatestTime(): ?string
    {
        return $this->latestTime;
    }

    /**
     * @return ?string
     */
    #[\Override]
    public function getSaveValue(): ?string
    {
        if ($this->getValue() === null) {
            return $this->isNullable() ? null : '';
        }

        return $this->getValue();
    }

    #[\Override]
    public function readValue(): static
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_string($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($value === '') {
                $this->value = null;
            } else {
                // Native time inputs submit the seconds if the `step` attribute
                // allows values below one minute. Invalid values are kept as is
                // to be reported during validation.
                $this->value = $this->normalizeTime($value) ?? $value;
            }
        }

        return $this;
    }

    #[\Override]
    public function validate(): void
    {
        if ($this->getValue() === null) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }

            return;
        }

        if ($this->normalizeTime($this->getValue()) === null) {
            $this->addValidationError(new FormFieldValidationError(
                'format',
                'wcf.form.field.time.error.format'
            ));

            return;
        }

        if ($this->earliestTime !== null && $this->getValue() < $this->earliestTime) {
            $this->addValidationError(new FormFieldValidationError(
                'minimum',
                'wcf.form.field.time.error.earliestTime',
                ['earliestTime' => $this->formatTime($this->earliestTime)]
            ));

            return;
        }

        if ($this->latestTime !== null && $this->getValue() > $this->latestTime) {
            $this->addValidationError(new FormFieldValidationError(
                'maximum',
                'wcf.form.field.time.error.latestTime',
                ['latestTime' => $this->formatTime($this->latestTime)]
            ));
        }
    }

    #[\Override]
    public function value(mixed $value): static
    {
        if ($value === null || $value === '') {
            $this->value = null;

            return $this;
        }

        $normalizedTime = \is_string($value) ? $this->normalizeTime($value) : null;
        if ($normalizedTime === null) {
            throw new \InvalidArgumentException(
                "Given value does not match format '" . static::TIME_FORMAT . "' for field '{$this->getId()}'."
            );
        }

        return parent::value($normalizedTime);
    }

    /**
     * @return string[]
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            // @phpstan-ignore staticClassAccess.privateMethod
            static::inputGetReservedFieldAttributes(),
            [
                'max',
                'min',
            ]
        );
    }
}
