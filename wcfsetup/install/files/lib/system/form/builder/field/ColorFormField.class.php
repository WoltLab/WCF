<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for a RGBA color.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
final class ColorFormField extends AbstractFormField implements IImmutableFormField
{
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_colorFormField';

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_string($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($this->value === '') {
                $this->value = null;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if ($this->getValue() === null) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } else {
            // @see StyleAddForm::readFormParameters()
            if (!\preg_match('~rgba\(\d{1,3}, ?\d{1,3}, ?\d{1,3}, ?(1|1\.00?|0|0?\.[0-9]{1,2})\)~', $this->getValue())) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalid',
                    'wcf.style.colorPicker.error.invalidColor'
                ));
            }
        }
    }
}
