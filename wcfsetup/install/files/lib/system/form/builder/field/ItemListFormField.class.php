<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\util\ArrayUtil;

/**
 * Implementation of a form field that allows entering a list of items.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class ItemListFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    IMultipleFormField
{
    use TInputAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TMultipleFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/ItemList';

    /**
     * type of the returned save value (see `SAVE_VALUE_TYPE_*` constants)
     * @var string
     */
    protected $saveValueType;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_itemListFormField';

    /**
     * save value return type so that an array with the item values will be returned
     * @var string
     */
    const SAVE_VALUE_TYPE_ARRAY = 'array';

    /**
     * save value return type so that comma-separated list with the item values
     * will be returned
     * @var string
     */
    const SAVE_VALUE_TYPE_CSV = 'csv';

    /**
     * save value return type so that newline-separated list with the item values
     * will be returned
     * @var string
     */
    const SAVE_VALUE_TYPE_NSV = 'nsv';

    /**
     * save value return type so that space-separated list with the item values
     * will be returned
     * @var string
     */
    const SAVE_VALUE_TYPE_SSV = 'ssv';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->multiple();
        $this->addFieldClass('long');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        switch ($this->getSaveValueType()) {
            case self::SAVE_VALUE_TYPE_ARRAY:
                return '';

            case self::SAVE_VALUE_TYPE_CSV:
                return \implode(',', $this->getValue() ?: []);

            case self::SAVE_VALUE_TYPE_NSV:
                return \implode("\n", $this->getValue() ?: []);

            case self::SAVE_VALUE_TYPE_SSV:
                return \implode(' ', $this->getValue() ?: []);

            default:
                throw new \LogicException("Unreachable");
        }
    }

    /**
     * Returns the type of the returned save value (see `SAVE_VALUE_TYPE_*` constants).
     *
     * If no save value type has been set, `SAVE_VALUE_TYPE_CSV` will be set and returned.
     *
     * @return  string
     */
    public function getSaveValueType()
    {
        if ($this->saveValueType === null) {
            $this->saveValueType = self::SAVE_VALUE_TYPE_CSV;
        }

        return $this->saveValueType;
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        // arrays cannot be returned as a simple save value
        return $this->getSaveValueType() !== self::SAVE_VALUE_TYPE_ARRAY;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        // an array should be passed as a parameter outside of the `data` array
        if ($this->getSaveValueType() === self::SAVE_VALUE_TYPE_ARRAY) {
            $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
                'itemList',
                function (IFormDocument $document, array $parameters) {
                    if ($this->checkDependencies() && \is_array($this->getValue())) {
                        $parameters[$this->getObjectProperty()] = $this->getValue();
                    }

                    return $parameters;
                }
            ));
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_array($value)) {
                $this->value = \array_unique(ArrayUtil::trim($value));
            }
        }

        return $this;
    }

    /**
     * Sets the type of the returned save value (see `SAVE_VALUE_TYPE_*` constants).
     *
     * @param string $saveValueType type of the returned save value
     * @return  static          this field
     * @throws  \BadMethodCallException         if save value type has already been set
     * @throws  \InvalidArgumentException       if given save value type is invalid
     */
    public function saveValueType($saveValueType)
    {
        if ($this->saveValueType !== null) {
            throw new \BadMethodCallException("Save value type has already been set for field '{$this->getId()}'.");
        }

        if (
            !\in_array($saveValueType, [
                self::SAVE_VALUE_TYPE_ARRAY,
                self::SAVE_VALUE_TYPE_CSV,
                self::SAVE_VALUE_TYPE_NSV,
                self::SAVE_VALUE_TYPE_SSV,
            ])
        ) {
            throw new \InvalidArgumentException("Unknown save value type '{$saveValueType}' for field '{$this->getId()}'.");
        }

        $this->saveValueType = $saveValueType;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        switch ($this->getSaveValueType()) {
            case self::SAVE_VALUE_TYPE_ARRAY:
                if (\is_array($value)) {
                    $this->value = $value;
                } else {
                    throw new InvalidFormFieldValue($this, 'array', \gettype($value));
                }

                break;

            case self::SAVE_VALUE_TYPE_CSV:
                if (\is_string($value)) {
                    $this->value = \explode(',', $value);
                } else {
                    throw new InvalidFormFieldValue($this, 'string', \gettype($value));
                }

                break;

            case self::SAVE_VALUE_TYPE_NSV:
                if (\is_string($value)) {
                    $this->value = \explode("\n", $value);
                } else {
                    throw new InvalidFormFieldValue($this, 'string', \gettype($value));
                }

                break;

            case self::SAVE_VALUE_TYPE_SSV:
                if (\is_string($value)) {
                    $this->value = \explode(' ', $value);
                } else {
                    throw new InvalidFormFieldValue($this, 'string', \gettype($value));
                }

                break;

            default:
                throw new \LogicException("Unreachable");
        }

        // If the input value is an empty string then the resulting array will
        // contain exactly one empty string element.
        $this->value = ArrayUtil::trim($this->value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if (\is_array($this->getValue())) {
            if ($this->getMinimumMultiples() > 0 && \count($this->getValue()) < $this->getMinimumMultiples()) {
                $this->addValidationError(new FormFieldValidationError(
                    'minimumMultiples',
                    'wcf.form.field.itemList.error.minimumMultiples',
                    [
                        'minimumCount' => $this->getMinimumMultiples(),
                        'count' => \count($this->getValue()),
                    ]
                ));
            } elseif (
                $this->getMaximumMultiples() !== IMultipleFormField::NO_MAXIMUM_MULTIPLES
                && \count($this->getValue()) > $this->getMaximumMultiples()
            ) {
                $this->addValidationError(new FormFieldValidationError(
                    'maximumMultiples',
                    'wcf.form.field.itemList.error.maximumMultiples',
                    [
                        'maximumCount' => $this->getMaximumMultiples(),
                        'count' => \count($this->getValue()),
                    ]
                ));
            }

            $invalidItems = [];
            foreach ($this->getValue() as $item) {
                switch ($this->getSaveValueType()) {
                    case self::SAVE_VALUE_TYPE_ARRAY:
                        // nothing
                        break;

                    case self::SAVE_VALUE_TYPE_CSV:
                        if (\strpos($item, ',') !== false) {
                            $invalidItems[] = $item;
                        }

                        break;

                    case self::SAVE_VALUE_TYPE_NSV:
                        if (\strpos($item, "\n") !== false) {
                            $invalidItems[] = $item;
                        }

                        break;

                    case self::SAVE_VALUE_TYPE_SSV:
                        if (\strpos($item, ' ') !== false) {
                            $invalidItems[] = $item;
                        }

                        break;

                    default:
                        throw new \LogicException("Unreachable");
                }
            }

            if (!empty($invalidItems)) {
                $separator = '';
                switch ($this->getSaveValue()) {
                    case self::SAVE_VALUE_TYPE_CSV:
                        $separator = ',';
                        break;

                    case self::SAVE_VALUE_TYPE_NSV:
                        $separator = "\n";
                        break;

                    case self::SAVE_VALUE_TYPE_SSV:
                        $separator = ' ';
                        break;
                }

                $this->addValidationError(new FormFieldValidationError(
                    'separator',
                    'wcf.form.field.itemList.error.separator',
                    [
                        'invalidItems' => $invalidItems,
                        'separator' => $separator,
                    ]
                ));
            }
        } elseif ($this->isRequired()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        parent::validate();
    }
}
