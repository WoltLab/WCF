<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Implementation of a form field for a date (with a time).
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class DateFormField extends AbstractFormField implements
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

    /**
     * earliest valid date as passed to `DateFormField::earliestDate()` or `null` if no earliest
     * valid date has been set
     * @var null|string|int|\DateTimeInterface
     */
    protected $earliestDate;

    /**
     * resolved earliest valid date or `null` if no earliest valid date has been set
     * @since 6.3
     */
    protected ?\DateTimeImmutable $earliestDateTime = null;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Date';

    /**
     * latest valid date as passed to `DateFormField::latestDate()` or `null` if no latest valid
     * date has been set
     * @var null|string|int|\DateTimeInterface
     */
    protected $latestDate;

    /**
     * resolved latest valid date or `null` if no latest valid date has been set
     * @since 6.3
     */
    protected ?\DateTimeImmutable $latestDateTime = null;

    /**
     * date time format of the save value
     * @var ?string
     */
    protected $saveValueFormat;

    /**
     * is `true` if not only the date, but also the time can be set
     * @var bool
     */
    protected $supportsTime = false;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_dateFormField';

    const DATE_FORMAT = 'Y-m-d';

    const TIME_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * format of the value of native `datetime-local` inputs
     * @since 6.3
     */
    const NATIVE_TIME_FORMAT = 'Y-m-d\TH:i';

    public function __construct()
    {
        $this->addFieldClass('medium');
    }

    /**
     * Sets the earliest valid date and returns this field. If `null` is given, the previously
     * set earliest valid date is unset.
     *
     * Integers are always treated as unix timestamps, strings have to be given in
     * `DateFormField::getSaveValueFormat()` format.
     *
     * @return  static
     */
    public function earliestDate(null|string|int|\DateTimeInterface $earliestDate = null)
    {
        $this->earliestDate = $earliestDate;
        $this->earliestDateTime = null;

        if ($earliestDate !== null) {
            $this->earliestDateTime = $this->resolveBoundary($earliestDate);
            if ($this->earliestDateTime === null) {
                throw new \InvalidArgumentException(
                    "Earliest date '{$earliestDate}' does not have save value format '{$this->getSaveValueFormat()}' for field '{$this->getId()}'."
                );
            }

            if ($this->latestDateTime !== null && $this->latestDateTime < $this->earliestDateTime) {
                $earliestDateString = $this->getBoundaryString($earliestDate);
                $latestDateString = $this->getBoundaryString($this->getLatestDate());

                throw new \InvalidArgumentException(
                    "Earliest date '{$earliestDateString}' cannot be later than latest date '{$latestDateString}' for field '{$this->getId()}'."
                );
            }
        }

        return $this;
    }

    /**
     * Returns the earliest valid date as passed to `DateFormField::earliestDate()`.
     *
     * If no earliest valid date has been set, `null` is returned.
     *
     * @return  null|string|int|\DateTimeInterface
     */
    public function getEarliestDate()
    {
        return $this->earliestDate;
    }

    #[\Override]
    public function getHtmlVariables()
    {
        // the native date input requires the `value`, `min` and `max` value to have
        // a specific format without a time zone offset
        $format = static::DATE_FORMAT;
        $timeZone = new \DateTimeZone('UTC');
        if ($this->supportsTime()) {
            $format = static::NATIVE_TIME_FORMAT;
            $timeZone = $this->getInputTimeZone();
        }

        $formattedValue = '';
        if ($this->getValue() !== null) {
            $dateTime = $this->getValueDateTimeObject();
            if ($dateTime !== null) {
                $formattedValue = $dateTime->setTimezone($timeZone)->format($format);
            }
        }

        $formattedEarliestDate = '';
        if ($this->earliestDateTime !== null) {
            $formattedEarliestDate = $this->earliestDateTime->setTimezone($timeZone)->format($format);
        }

        $formattedLatestDate = '';
        if ($this->latestDateTime !== null) {
            $formattedLatestDate = $this->latestDateTime->setTimezone($timeZone)->format($format);
        }

        return [
            'dateFormFieldValue' => $formattedValue,
            'dateFormFieldEarliestDate' => $formattedEarliestDate,
            'dateFormFieldLatestDate' => $formattedLatestDate,
        ];
    }

    /**
     * Returns the time zone in which the native date time input displays and
     * submits its value.
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
     * Returns the latest valid date as passed to `DateFormField::latestDate()`.
     *
     * If no latest valid date has been set, `null` is returned.
     *
     * @return  null|string|int|\DateTimeInterface
     */
    public function getLatestDate()
    {
        return $this->latestDate;
    }

    /**
     * Returns the type of the returned save value.
     *
     * If no save value format has been set, `U` (unix timestamp) is returned.
     *
     * @return  string
     */
    public function getSaveValueFormat()
    {
        return $this->saveValueFormat ?? 'U';
    }

    /**
     * Returns a date time object for the current value or `null` if no date time
     * object could be created.
     *
     * @return  \DateTime|null
     */
    protected function getValueDateTimeObject()
    {
        // The `!` prefix resets all fields not present in the format, otherwise
        // a date without a time would implicitly receive the current time.
        if ($this->supportsTime()) {
            $dateTime = \DateTime::createFromFormat(
                '!' . static::TIME_FORMAT,
                $this->getValue(),
                new \DateTimeZone('UTC')
            );
        } else {
            $dateTime = \DateTime::createFromFormat(
                '!' . static::DATE_FORMAT,
                $this->getValue(),
                new \DateTimeZone('UTC')
            );
        }

        if ($dateTime === false) {
            return null;
        }

        return $dateTime;
    }

    /**
     * @return ?string
     */
    #[\Override]
    public function getSaveValue()
    {
        if ($this->getValue() === null) {
            if ($this->isNullable()) {
                return null;
            } else {
                return DateUtil::getDateTimeByTimestamp(0)->format($this->getSaveValueFormat());
            }
        }

        return $this->getValueDateTimeObject()->format($this->getSaveValueFormat());
    }

    /**
     * Sets the latest valid date and returns this field. If `null` is given, the previously
     * set latest valid date is unset.
     *
     * Integers are always treated as unix timestamps, strings have to be given in
     * `DateFormField::getSaveValueFormat()` format.
     *
     * @return  static
     */
    public function latestDate(null|string|int|\DateTimeInterface $latestDate = null)
    {
        $this->latestDate = $latestDate;
        $this->latestDateTime = null;

        if ($latestDate !== null) {
            $this->latestDateTime = $this->resolveBoundary($latestDate);
            if ($this->latestDateTime === null) {
                throw new \InvalidArgumentException(
                    "Latest date '{$latestDate}' does not have save value format '{$this->getSaveValueFormat()}' for field '{$this->getId()}'."
                );
            }

            if ($this->earliestDateTime !== null && $this->latestDateTime < $this->earliestDateTime) {
                $latestDateString = $this->getBoundaryString($latestDate);
                $earliestDateString = $this->getBoundaryString($this->getEarliestDate());

                throw new \InvalidArgumentException(
                    "Latest date '{$latestDateString}' cannot be earlier than earliest date '{$earliestDateString}' for field '{$this->getId()}'."
                );
            }
        }

        return $this;
    }

    #[\Override]
    public function readValue()
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_string($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());
            $this->value = $value;

            if ($this->value === '') {
                $this->value = null;
            } else {
                // Native `datetime-local` inputs submit the value without a
                // time zone offset in the time zone of the input, optionally
                // including the seconds. The legacy JavaScript component also
                // omits the time zone if it has been told to ignore it.
                $isValidTime = false;
                if ($this->supportsTime()) {
                    foreach (['!' . static::NATIVE_TIME_FORMAT, '!Y-m-d\TH:i:s'] as $format) {
                        $dateTime = \DateTime::createFromFormat(
                            $format,
                            $this->getValue(),
                            $this->getInputTimeZone()
                        );

                        if ($dateTime !== false) {
                            $isValidTime = true;

                            $this->value = $dateTime
                                ->setTimezone(new \DateTimeZone('UTC'))
                                ->format(self::TIME_FORMAT);

                            break;
                        }
                    }
                }

                if (!$isValidTime && $this->getValueDateTimeObject() === null) {
                    try {
                        $this->value($value);
                    } catch (\InvalidArgumentException $e) {
                        $this->value = null;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Sets the date time format of the save value.
     *
     * @return  static
     */
    public function saveValueFormat(string $saveValueFormat)
    {
        if ($this->saveValueFormat !== null) {
            throw new \BadMethodCallException("Save value type has already been set for field '{$this->getId()}'.");
        }

        $this->saveValueFormat = $saveValueFormat;

        return $this;
    }

    /**
     * Sets if not only the date, but also the time can be set.
     *
     * @return  static      this field
     */
    public function supportTime(bool $supportsTime = true)
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
     *
     * @return  bool
     */
    public function supportsTime()
    {
        return $this->supportsTime;
    }

    #[\Override]
    public function validate()
    {
        if ($this->getValue() === null) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } else {
            $dateTime = $this->getValueDateTimeObject();
            if ($dateTime === null) {
                $this->addValidationError(new FormFieldValidationError(
                    'format',
                    'wcf.form.field.date.error.format'
                ));

                return;
            }

            if ($this->earliestDateTime !== null) {
                $earliestDateTime = $this->getBoundaryDateTimeObject($this->earliestDateTime);

                if ($dateTime < $earliestDateTime) {
                    $this->addValidationError(new FormFieldValidationError(
                        'minimum',
                        'wcf.form.field.date.error.earliestDate',
                        ['earliestDate' => $this->getDateTimeFormatter()->format($earliestDateTime)]
                    ));

                    return;
                }
            }

            if ($this->latestDateTime !== null) {
                $latestDateTime = $this->getBoundaryDateTimeObject($this->latestDateTime);

                if ($dateTime > $latestDateTime) {
                    $this->addValidationError(new FormFieldValidationError(
                        'minimum',
                        'wcf.form.field.date.error.latestDate',
                        ['latestDate' => $this->getDateTimeFormatter()->format($latestDateTime)]
                    ));

                    return;
                }
            }
        }
    }

    #[\Override]
    public function value(mixed $value)
    {
        // Non-nullable fields store an empty value as `0`, which must be
        // restored as an empty value instead of 1970-01-01.
        if (
            !$this->isNullable()
            && $this->getSaveValueFormat() === 'U'
            && (int)$value === 0
        ) {
            $this->value = null;

            return $this;
        }

        parent::value($value);

        $dateTime = $this->parseSaveValue($this->getValue());
        if ($dateTime === null) {
            throw new \InvalidArgumentException(
                "Given value does not match format '{$this->getSaveValueFormat()}' for field '{$this->getId()}'."
            );
        }

        if ($this->supportsTime()) {
            parent::value($dateTime->format(static::TIME_FORMAT));
        } else {
            parent::value($dateTime->format(static::DATE_FORMAT));
        }

        return $this;
    }

    /**
     * @return string[]
     * @since 5.4
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

    /**
     * Returns the date time object used to validate the value against the given
     * earliest or latest valid date. Without time support, the time is reset to
     * the start of the day to allow the day of the boundary itself to be selected.
     *
     * @since 6.3
     */
    protected function getBoundaryDateTimeObject(\DateTimeImmutable $dateTime): \DateTimeImmutable
    {
        if (!$this->supportsTime()) {
            return $dateTime->setTimezone(new \DateTimeZone('UTC'))->setTime(0, 0);
        }

        return $dateTime;
    }

    /**
     * Returns a date time object for an earliest or latest valid date or `null`
     * if the given string does not match the save value format.
     *
     * @since 6.3
     */
    protected function resolveBoundary(string|int|\DateTimeInterface $date): ?\DateTimeImmutable
    {
        if ($date instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($date);
        }

        if (\is_int($date)) {
            return new \DateTimeImmutable('@' . $date);
        }

        return $this->parseSaveValue($date);
    }

    /**
     * Returns a date time object for a value in save value format or `null` if
     * the value does not match the format.
     *
     * @since 6.3
     */
    protected function parseSaveValue(string|int $value): ?\DateTimeImmutable
    {
        // The `!` prefix resets all fields not present in the format, otherwise
        // a date without a time would implicitly receive the current time.
        $dateTime = \DateTimeImmutable::createFromFormat(
            '!' . $this->getSaveValueFormat(),
            (string)$value,
            new \DateTimeZone('UTC')
        );

        if ($dateTime === false) {
            return null;
        }

        return $dateTime;
    }

    private function getBoundaryString(string|int|\DateTimeInterface $date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format(\DateTimeInterface::ATOM);
        }

        return (string)$date;
    }

    /**
     * Returns an instance of `\IntlDateFormatter' for formatting `\DateTime` objects.
     * The formatter displays the date and time (if supported) in the user's locale and timezone.
     * Dates without a time are displayed in UTC, matching the stored value.
     *
     * @since 6.2
     */
    protected function getDateTimeFormatter(): \IntlDateFormatter
    {
        $timeFormat = \IntlDateFormatter::NONE;
        $timeZone = new \DateTimeZone('UTC');
        if ($this->supportsTime()) {
            $timeFormat = \IntlDateFormatter::SHORT;
            $timeZone = $this->getInputTimeZone();
        }

        return new \IntlDateFormatter(
            WCF::getLanguage()->getLocale(),
            \IntlDateFormatter::LONG,
            $timeFormat,
            $timeZone
        );
    }
}
