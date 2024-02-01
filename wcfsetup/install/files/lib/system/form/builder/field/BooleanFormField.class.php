<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for boolean values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class BooleanFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField
{
    use TInputAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Checked';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_booleanFormField';

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        return $this->value ? 1 : 0;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = \intval($this->getDocument()->getRequestData($this->getPrefixedId())) === 1;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function requiresLabel()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->isRequired() && !$this->getValue()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        if (\is_string($value) && \in_array($value, ['0', '1', 'true', 'false'])) {
            $value = ($value === '1' || $value === 'true');
        }
        if (\is_int($value) && ($value === 0 || $value === 1)) {
            $value = ($value === 1);
        } elseif (!\is_bool($value)) {
            throw new InvalidFormFieldValue($this, 'bool', \gettype($value));
        }

        return parent::value($value);
    }
}
