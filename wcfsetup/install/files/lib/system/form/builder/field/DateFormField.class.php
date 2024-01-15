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
     * @var string
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
    protected $templateName = '__dateFormField';

    const DATE_FORMAT = 'Y-m-d';

    const TIME_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * Creates a new instance of `DateFormField`.
     */
    public function __construct()
    {
        $this->addFieldClass('medium');
    }

    /**
     * Sets the earliest valid date in `DateFormField::$saveValueFormat` format and returns this
     * field. If `null` is given, the previously set earliest valid date is unset.
     *
     * @param null|string|int $earliestDate
     * @return  static
     */
    public function earliestDate($earliestDate = null)
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

    /**
     * @inheritDoc
     */
    public function getHtmlVariables()
    {
        // the date picker JavaScript code requires the `min` and `max` value to have a
        // specific format which is easier to create in PHP than in the template itself

        $format = static::DATE_FORMAT;
        if ($this->supportsTime()) {
            $format = static::TIME_FORMAT;
        }

        $formattedEarliestDate = '';
        if ($this->getEarliestDate() !== null) {
            $formattedEarliestDate = \DateTime::createFromFormat(
                $this->getSaveValueFormat(),
                $this->getEarliestDate(),
                new \DateTimeZone('UTC')
            )->format($format);
        }

        $formattedLatestDate = '';
        if ($this->getLatestDate() !== null) {
            $formattedLatestDate = \DateTime::createFromFormat(
                $this->getSaveValueFormat(),
                $this->getLatestDate(),
                new \DateTimeZone('UTC')
            )->format($format);
        }

        return [
            'dateFormFieldEarliestDate' => $formattedEarliestDate,
            'dateFormFieldLatestDate' => $formattedLatestDate,
        ];
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
        if ($this->supportsTime()) {
            $dateTime = \DateTime::createFromFormat(
                static::TIME_FORMAT,
                $this->getValue(),
                new \DateTimeZone('UTC')
            );
        } else {
            $dateTime = \DateTime::createFromFormat(
                static::DATE_FORMAT,
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
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if ($this->getValue() === null) {
            if ($this->isNullable()) {
                return;
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
     * @param null|string|int $latestDate
     * @return  static
     */
    public function latestDate($latestDate = null)
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

    /**
     * @inheritDoc
     */
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
            } elseif ($this->getValueDateTimeObject() === null) {
                try {
                    $this->value($value);
                } catch (\InvalidArgumentException) {
                    $this->value = null;
                }
            }
        }

        return $this;
    }

    /**
     * Sets the date time format of the save value.
     *
     * @param string $saveValueFormat
     * @return  static
     */
    public function saveValueFormat($saveValueFormat)
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
     * @param bool $supportsTime
     * @return  static      this field
     */
    public function supportTime($supportsTime = true)
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
            $dateTime = $this->getValueDateTimeObject();
            if ($dateTime === null) {
                $this->addValidationError(new FormFieldValidationError(
                    'format',
                    'wcf.form.field.date.error.format'
                ));

                return;
            }

            if ($this->getEarliestDate() !== null) {
                $earliestDateTime = \DateTime::createFromFormat(
                    $this->getSaveValueFormat(),
                    $this->getEarliestDate(),
                    new \DateTimeZone('UTC')
                );

                if ($dateTime < $earliestDateTime) {
                    $format = DateUtil::DATE_FORMAT;
                    if ($this->supportsTime()) {
                        $format = \str_replace(
                            ['%date%', '%time%'],
                            [
                                WCF::getLanguage()->get(DateUtil::DATE_FORMAT),
                                WCF::getLanguage()->get(DateUtil::TIME_FORMAT),
                            ],
                            WCF::getLanguage()->get('wcf.date.dateTimeFormat')
                        );
                    }

                    $this->addValidationError(new FormFieldValidationError(
                        'minimum',
                        'wcf.form.field.date.error.earliestDate',
                        ['earliestDate' => DateUtil::format($earliestDateTime, $format)]
                    ));

                    return;
                }
            }

            if ($this->getLatestDate() !== null) {
                $latestDateTime = \DateTime::createFromFormat(
                    $this->getSaveValueFormat(),
                    $this->getLatestDate(),
                    new \DateTimeZone('UTC')
                );

                if ($dateTime > $latestDateTime) {
                    $format = DateUtil::DATE_FORMAT;
                    if ($this->supportsTime()) {
                        $format = \str_replace(
                            ['%date%', '%time%'],
                            [
                                WCF::getLanguage()->get(DateUtil::DATE_FORMAT),
                                WCF::getLanguage()->get(DateUtil::TIME_FORMAT),
                            ],
                            WCF::getLanguage()->get('wcf.date.dateTimeFormat')
                        );
                    }

                    $this->addValidationError(new FormFieldValidationError(
                        'minimum',
                        'wcf.form.field.date.error.latestDate',
                        ['latestDate' => DateUtil::format($latestDateTime, $format)]
                    ));

                    return;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
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
     * @inheritDoc
     * @since       5.4
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            static::inputGetReservedFieldAttributes(),
            [
                'max',
                'min',
            ]
        );
    }
}
