<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Abstract implementation of a form field for numeric values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
abstract class AbstractNumericFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoCompleteFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    IInputModeFormField,
    IMaximumFormField,
    IMinimumFormField,
    INullableFormField,
    IPlaceholderFormField,
    ISuffixedFormField
{
    use TAttributeFormField {
        getReservedFieldAttributes as private defaultGetReservedFieldAttributes;
    }
    use TAutoCompleteFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TInputModeFormField;
    use TMaximumFormField;
    use TMinimumFormField;
    use TNullableFormField;
    use TPlaceholderFormField;
    use TSuffixedFormField;

    /**
     * is `true` if only integer values are supported
     * @var bool
     */
    protected $integerValues = false;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * step value for the input element
     * @var null|number
     */
    protected $step;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_numericFormField';

    /**
     * Creates a new instance of `AbstractNumericFormField`.
     */
    public function __construct()
    {
        $this->addFieldClass('short');
    }

    /**
     * Returns the default value for the input element's step attribute.
     *
     * @return  number|string
     */
    protected function getDefaultStep()
    {
        if ($this->integerValues) {
            return 1;
        } else {
            return 'any';
        }
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if ($this->getValue() === null && !$this->isNullable()) {
            if ($this->integerValues) {
                return 0;
            } else {
                return 0.0;
            }
        }

        return parent::getSaveValue();
    }

    /**
     * Returns the value for the input element's step attribute. This method
     * can either return a number or `any` if no specific step is defined.
     *
     * If no step value has been set, the return value of `getDefaultStep()`
     * is set and returned.
     *
     * @return  number|string
     */
    public function getStep()
    {
        if ($this->step === null) {
            $this->step = $this->getDefaultStep();
        }

        return $this->step;
    }

    /**
     * @inheritDoc
     */
    protected function getValidInputModes(): array
    {
        if ($this->integerValues) {
            return ['numeric'];
        }

        return ['decimal'];
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($value !== '') {
                if ($this->integerValues) {
                    $this->value = \intval($value);
                } else {
                    $this->value = \floatval($value);
                }
            }
        }

        return $this;
    }

    /**
     * Sets the value for the input element's step attribute.
     *
     * @param null|number|string $step
     * @return  static
     *
     * @throws  \InvalidArgumentException   if the given step value is invalid
     */
    public function step($step = null)
    {
        if ($step !== null) {
            if ($this->integerValues) {
                if (!\is_int($step)) {
                    throw new \InvalidArgumentException(
                        "Given step is no int, '" . \gettype($step) . "' given for field '{$this->getId()}'."
                    );
                }
            } else {
                if (\is_string($step) && $step !== 'any') {
                    throw new \InvalidArgumentException(
                        "The only valid step value is 'any', '" . $step . "' given for field '{$this->getId()}'."
                    );
                } elseif (!\is_numeric($step)) {
                    throw new \InvalidArgumentException(
                        "Given step is no number, '" . \gettype($step) . "' given for field '{$this->getId()}'."
                    );
                }
            }
        }

        $this->step = $step;

        return $this;
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
            if ($this->getMinimum() !== null && $this->getValue() < $this->getMinimum()) {
                $this->addValidationError(new FormFieldValidationError(
                    'minimum',
                    'wcf.form.field.numeric.error.minimum',
                    ['minimum' => $this->getMinimum()]
                ));
            } elseif ($this->getMaximum() !== null && $this->getValue() > $this->getMaximum()) {
                $this->addValidationError(new FormFieldValidationError(
                    'maximum',
                    'wcf.form.field.numeric.error.maximum',
                    ['maximum' => $this->getMaximum()]
                ));
            }
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        if ($value !== null) {
            if (\is_string($value) && \is_numeric($value)) {
                if (\preg_match('~^-?\d+$~', $value)) {
                    $value = \intval($value);
                } else {
                    $value = \floatval($value);
                }
            }

            if ($this->integerValues && !\is_int($value)) {
                throw new InvalidFormFieldValue($this, 'int or `null`', \gettype($value));
            } elseif (!$this->integerValues && !\is_numeric($value)) {
                throw new InvalidFormFieldValue($this, 'number or `null`', \gettype($value));
            }
        }

        return parent::value($value);
    }

    /**
     * @inheritDoc
     * @since       5.4
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            static::defaultGetReservedFieldAttributes(),
            [
                'step',
            ]
        );
    }
}
