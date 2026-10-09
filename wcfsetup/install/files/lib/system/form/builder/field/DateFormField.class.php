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
     * earliest valid date in `DateFormField::$saveValueFormat` format or `null` if no earliest
     * valid date has been set
     * @var null|string|int
     */
    protected $earliestDate;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Date';

    /**
     * latest valid date in `DateFormField::$saveValueFormat` format or `null` if no latest valid
     * date has been set
     * @var null|string|int
     */
    protected $latestDate;

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
     * Sets the earliest valid date in `DateFormField::$saveValueFormat` format and returns this
     * field. If `null` is given, the previously set earliest valid date is unset.
     *
     * @return  static
     */
    public function earliestDate(null|string|int $earliestDate = null)
    {
        $this->earliestDate = $earliestDate;

        if ($this->earliestDate !== null) {
            $earliestDateTime = \DateTime::createFromFormat(
                $this->getSaveValueFormat(),
                $this->earliestDate,
                new \DateTimeZone('UTC')
            );
            if ($earliestDateTime === false) {
                throw new \InvalidArgumentException(
                    "Earliest date '{$this->earliestDate}' does not have save value format '{$this->getSaveValueFormat()}' for field '{$this->getId()}'."
                );
            }

            if ($this->getLatestDate() !== null) {
                $latestDateTime = \DateTime::createFromFormat(
                    $this->getSaveValueFormat(),
                    $this->getLatestDate(),
                    new \DateTimeZone('UTC')
                );

                if ($latestDateTime < $earliestDateTime) {
                    throw new \InvalidArgumentException(
                        "Earliest date '{$this->earliestDate}' cannot be later than latest date '{$this->getLatestDate()}' for field '{$this->getId()}'."
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Returns the earliest valid date in `DateFormField::getSaveValueFormat()` format.
     *
     * If no earliest valid date has been set, `null` is returned.
     *
     * @return  null|string|int
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
        if ($this->getEarliestDate() !== null) {
            $formattedEarliestDate = \DateTime::createFromFormat(
                $this->getSaveValueFormat(),
                $this->getEarliestDate(),
                new \DateTimeZone('UTC')
            )->setTimezone($timeZone)->format($format);
        }

        $formattedLatestDate = '';
        if ($this->getLatestDate() !== null) {
            $formattedLatestDate = \DateTime::createFromFormat(
                $this->getSaveValueFormat(),
                $this->getLatestDate(),
                new \DateTimeZone('UTC')
            )->setTimezone($timeZone)->format($format);
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
     * Returns the latest valid date in `DateFormField::getSaveValueFormat()` format.
     *
     * If no latest valid date has been set, `null` is returned.
     *
     * @return  null|string|int
     */
    public function getLatestDate()
    {
        return $this->latestDate;
    }

    /**
     * Returns the type of the returned save value.
     *
     * If no save value format has been set, `U` (unix timestamp) will be set and returned.
     *
     * @return  string
     */
    public function getSaveValueFormat()
    {
        if ($this->saveValueFormat === null) {
            $this->saveValueFormat = 'U';
        }

        return $this->saveValueFormat;
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
     * Sets the latest valid date in `DateFormField::$saveValueFormat` format and returns this
     * field. If `null` is given, the previously set latest valid date is unset.
     *
     * @return  static
     */
    public function latestDate(null|string|int $latestDate = null)
    {
        $this->latestDate = $latestDate;

        if ($this->latestDate !== null) {
            $latestDateTime = \DateTime::createFromFormat(
                $this->getSaveValueFormat(),
                $this->latestDate,
                new \DateTimeZone('UTC')
            );

            if ($latestDateTime === false) {
                throw new \InvalidArgumentException(
                    "Latest date '{$this->latestDate}' does not have save value format '{$this->getSaveValueFormat()}' for field '{$this->getId()}'."
                );
            }

            if ($this->getEarliestDate() !== null) {
                $earliestDateTime = \DateTime::createFromFormat(
                    $this->getSaveValueFormat(),
                    $this->getEarliestDate(),
                    new \DateTimeZone('UTC')
                );

                if ($latestDateTime < $earliestDateTime) {
                    throw new \InvalidArgumentException(
                        "Latest date '{$this->latestDate}' cannot be earlier than earliest date '{$this->getEarliestDate()}' for field '{$this->getId()}'."
                    );
                }
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

            if ($this->getEarliestDate() !== null) {
                $earliestDateTime = $this->getBoundaryDateTimeObject($this->getEarliestDate());

                if ($dateTime < $earliestDateTime) {
                    $this->addValidationError(new FormFieldValidationError(
                        'minimum',
                        'wcf.form.field.date.error.earliestDate',
                        ['earliestDate' => $this->getDateTimeFormatter()->format($earliestDateTime)]
                    ));

                    return;
                }
            }

            if ($this->getLatestDate() !== null) {
                $latestDateTime = $this->getBoundaryDateTimeObject($this->getLatestDate());

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

        $dateTime = \DateTime::createFromFormat(
            $this->getSaveValueFormat(),
            $this->getValue(),
            new \DateTimeZone('UTC')
        );
        if ($dateTime === false) {
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
     * Returns a date time object for the given earliest or latest valid date.
     * Without time support, the time is reset to the start of the day to allow
     * the day of the boundary itself to be selected.
     *
     * @since 6.3
     */
    protected function getBoundaryDateTimeObject(string|int $date): \DateTime
    {
        $dateTime = \DateTime::createFromFormat(
            $this->getSaveValueFormat(),
            (string)$date,
            new \DateTimeZone('UTC')
        );

        if (!$this->supportsTime()) {
            $dateTime->setTimezone(new \DateTimeZone('UTC'))->setTime(0, 0);
        }

        return $dateTime;
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
