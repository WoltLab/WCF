<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a radio buttons form field for selecting a single value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class RadioButtonFormField extends AbstractFormField implements
    IAttributeFormField,
    ICssClassFormField,
    IImmutableFormField,
    ISelectionFormField
{
    use TInputAttributeFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TSelectionFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/RadioButton';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_radioButtonFormField';

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
    public function supportsNestedOptions()
    {
        return false;
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
}
