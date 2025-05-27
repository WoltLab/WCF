<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DateConditionFormField extends AbstractConditionFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    INullableFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TNullableFormField;

    public const DATE_FORMAT = 'Y-m-d';

    public const TIME_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * is `true` if not only the date, but also the time can be set
     */
    protected bool $supportsTime = false;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_dateConditionFormField';

    public function __construct()
    {
        $this->fieldAttribute("data-ignore-timezone", "1");
    }

    #[\Override]
    public function getSaveValue()
    {
        if ($this->getValue() === null) {
            return parent::getSaveValue();
        }

        $dateTime = \DateTime::createFromFormat(
            $this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT,
            $this->getValue(),
            WCF::getUser()->getTimezone()
        );

        return [
            "condition" => $this->getCondition(),
            "value" => $dateTime->getTimestamp(),
        ];
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        if ($this->getValue() !== null) {
            $dateTime = \DateTime::createFromFormat(
                $this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT,
                $this->getValue(),
                WCF::getUser()->getTimezone()
            );

            if ($dateTime === false) {
                $this->addValidationError(
                    new FormFieldValidationError(
                        'format',
                        'wcf.form.field.date.error.format'
                    )
                );
            }
        }
    }

    #[\Override]
    public function value($value): self
    {
        parent::value($value);

        if ($this->getValue() !== null) {
            $dateTime = DateUtil::getDateTimeByTimestamp($this->getValue());
            $dateTime->setTimezone(WCF::getUser()->getTimezone());
            $this->value["value"] = $dateTime->format($this->supportsTime() ? self::TIME_FORMAT : self::DATE_FORMAT);
        }

        return $this;
    }

    /**
     * Sets if not only the date, but also the time can be set.
     */
    public function supportTime(bool $supportsTime = true): self
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
}
