<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for a password.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
class PasswordFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoCompleteFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    IInputModeFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField,
    IPatternFormField,
    IPlaceholderFormField
{
    use TInputAttributeFormField;
    use TTextAutoCompleteFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TDefaultIdFormField;
    use TImmutableFormField;
    use TInputModeFormField;
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TPatternFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_passwordFormField';

    /**
     * Creates a new instance of `PasswordFormField`.
     */
    public function __construct()
    {
        $this->label('wcf.user.password');
        $this->addFieldClass('medium');
    }

    /**
     * @inheritDoc
     */
    protected function getValidInputModes(): array
    {
        return [
            'text',
        ];
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $value = $this->getValue();
        $hasValue = $this->getValue() !== null && $this->getValue() !== '';

        if ($this->isRequired() && !$hasValue) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } elseif ($hasValue) {
            $this->validateMinimumLength($value);
            $this->validateMaximumLength($value);
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'password';
    }

    /**
     * @inheritDoc
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return ['new-password', 'current-password'];
    }
}
