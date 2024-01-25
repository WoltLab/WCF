<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for selecting multiple values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class MultipleSelectionFormField extends AbstractFormField implements
    IAttributeFormField,
    ICssClassFormField,
    IFilterableSelectionFormField,
    IImmutableFormField
{
    use TInputAttributeFormField;
    use TCssClassFormField;
    use TFilterableSelectionFormField;
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Checkboxes';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_multipleSelectionFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'multiple',
                function (IFormDocument $document, array $parameters) {
                    if ($this->checkDependencies() && !empty($this->getValue())) {
                        $parameters[$this->getObjectProperty()] = $this->getValue();
                    }

                    return $parameters;
                }
            )
        );

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
                $this->value = $value;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $value = $this->getValue();

        if (($value === null || empty($value)) && $this->isRequired()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } elseif ($value !== null && !empty(\array_diff($this->getValue(), \array_keys($this->getOptions())))) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        // ignore `null` as value which can be passed either for nullable
        // fields or as value if no options are available
        if ($value === null) {
            return $this;
        }

        if (!\is_array($value)) {
            throw new InvalidFormFieldValue($this, 'array', \gettype($value));
        }

        $unknownValues = \array_diff($value, \array_keys($this->getOptions()));
        if (!empty($unknownValues)) {
            throw new \InvalidArgumentException(
                "Unknown values '" . \implode("', '", $unknownValues) . "' for field '{$this->getId()}'."
            );
        }

        return parent::value($value);
    }
}
