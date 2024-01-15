<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for selecting a single value.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class SelectFormField extends AbstractFormField implements
    ICssClassFormField,
    IImmutableFormField,
    ISelectionFormField
{
    use TCssClassFormField;
    use TImmutableFormField;
    use TSelectionFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = '__selectFormField';

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
                $this->value = $value !== '' ? $value : null;
            }
        }

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
            if (!isset($this->getOptions()[$this->getValue()])) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalidValue',
                    'wcf.global.form.error.noValidSelection'
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
            if (!isset($this->getOptions()[$value])) {
                throw new \InvalidArgumentException("Unknown value '{$value}' for field '{$this->getId()}'.");
            }
        }

        return parent::value($value);
    }
}
