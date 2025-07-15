<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\util\DateUtil;

/**
 * Implementation of a form field for a time.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class TimeFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    INullableFormField
{
    use TInputAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TNullableFormField;

    public const FORMAT = 'H:i';
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Date';
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_timeFormField';

    public function __construct()
    {
        $this->addFieldClass('medium')
            // If no value is set for the time, the time selection cannot be used.
            ->value('00:00');
    }

    #[\Override]
    public function getSaveValue()
    {
        if ($this->getValue() === null) {
            if ($this->isNullable()) {
                return;
            } else {
                return DateUtil::getDateTimeByTimestamp(0)->format(self::FORMAT);
            }
        }

        return $this->getValueDateTimeObject()->format(self::FORMAT);
    }

    #[\Override]
    public function validate()
    {
        if ($this->getValue() === null) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        }
    }

    #[\Override]
    public function value($value)
    {
        parent::value($value);

        $dateTime = \DateTimeImmutable::createFromFormat(
            self::FORMAT,
            $this->getValue(),
        );
        if ($dateTime === false) {
            throw new \InvalidArgumentException(
                "Given value does not match format '" . self::FORMAT . "' for field '{$this->getId()}'."
            );
        }

        parent::value($dateTime->format(self::FORMAT));

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

    private function getValueDateTimeObject(): ?\DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat('H:i', $this->getValue());

        if ($dateTime === false) {
            return null;
        }

        return $dateTime;
    }
}
