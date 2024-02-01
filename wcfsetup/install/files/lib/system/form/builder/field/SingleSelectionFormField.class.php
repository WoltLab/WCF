<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for selecting a single value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class SingleSelectionFormField extends AbstractFormField implements
    ICssClassFormField,
    IImmutableFormField,
    IFilterableSelectionFormField,
    INullableFormField
{
    use TCssClassFormField;
    use TImmutableFormField;
    use TFilterableSelectionFormField {
        filterable as protected traitFilterable;
    }
    use TNullableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_singleSelectionFormField';

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if (
            empty($this->getValue())
            && isset($this->getOptions()[$this->getValue()])
            && $this instanceof INullableFormField
            && $this->isNullable()
        ) {
            return;
        }

        return parent::getSaveValue();
    }

    /**
     * @inheritDoc
     */
    public function filterable($filterable = true)
    {
        if ($filterable) {
            $this->javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/RadioButton';
        } else {
            $this->javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
        }

        return $this->traitFilterable($filterable);
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
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
        if (!isset($this->getOptions()[$this->getValue()])) {
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

        if (!isset($this->getOptions()[$value])) {
            throw new \InvalidArgumentException("Unknown value '{$value}' for field '{$this->getId()}'.");
        }

        return parent::value($value);
    }
}
