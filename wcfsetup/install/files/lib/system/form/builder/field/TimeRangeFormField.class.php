<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for a range of times of day without a date.
 *
 * Both times are wall-clock times in `H:i` format that are not subject to any
 * time zone conversion, either time may be empty. The two values are separated
 * by a semicolon in the value. The start time may be later than the end time,
 * which can be used to represent a range spanning midnight.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class TimeRangeFormField extends AbstractFormField implements
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
    use TTimeValueFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/DateRange';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_timeRangeFormField';

    public function __construct()
    {
        $this->addFieldClass('small');
    }

    /**
     * @return ?string
     */
    #[\Override]
    public function getSaveValue(): ?string
    {
        if ($this->getFromValue() === '' && $this->getToValue() === '' && $this->isNullable()) {
            return null;
        }

        return $this->getFromValue() . ';' . $this->getToValue();
    }

    #[\Override]
    public function readValue(): static
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            // Invalid values are kept as is to be reported during validation.
            $from = \is_string($value['from'] ?? null) ? $value['from'] : '';
            $to = \is_string($value['to'] ?? null) ? $value['to'] : '';

            $this->value = [
                'from' => $from === '' ? '' : ($this->normalizeTime($from) ?? $from),
                'to' => $to === '' ? '' : ($this->normalizeTime($to) ?? $to),
            ];
        }

        return $this;
    }

    #[\Override]
    public function validate(): void
    {
        if ($this->isRequired() && ($this->getFromValue() === '' || $this->getToValue() === '')) {
            $this->addValidationError(new FormFieldValidationError('empty'));

            return;
        }

        foreach ([$this->getFromValue(), $this->getToValue()] as $time) {
            if ($time !== '' && $this->normalizeTime($time) === null) {
                $this->addValidationError(new FormFieldValidationError(
                    'format',
                    'wcf.form.field.time.error.format'
                ));

                return;
            }
        }
    }

    #[\Override]
    public function value(mixed $value): static
    {
        if ($value === null) {
            $this->value = null;

            return $this;
        }

        $values = \is_string($value) ? \explode(';', $value) : [];
        if (\count($values) !== 2) {
            throw new \InvalidArgumentException(
                "Given value does not match format 'H:i;H:i' for field '{$this->getId()}'."
            );
        }

        $times = [];
        foreach (['from' => $values[0], 'to' => $values[1]] as $key => $time) {
            if ($time === '') {
                $times[$key] = '';

                continue;
            }

            $normalizedTime = $this->normalizeTime($time);
            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(
                    "Given value does not match format 'H:i;H:i' for field '{$this->getId()}'."
                );
            }

            $times[$key] = $normalizedTime;
        }

        $this->value = $times;

        return $this;
    }

    /**
     * Returns the start time in `H:i` format or an empty string if no start
     * time has been set.
     */
    public function getFromValue(): string
    {
        return $this->value['from'] ?? '';
    }

    /**
     * Returns the end time in `H:i` format or an empty string if no end time
     * has been set.
     */
    public function getToValue(): string
    {
        return $this->value['to'] ?? '';
    }
}
