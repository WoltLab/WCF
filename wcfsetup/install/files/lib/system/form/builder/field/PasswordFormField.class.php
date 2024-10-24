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

    protected bool $strengthMeter = true;
    /**
     * @var IFormField[]
     */
    protected array $relatedFields = [];
    /**
     * @var string[]
     */
    protected array $relatedFieldsId = [];

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
     * Sets if the password strength meter should be used to provide feedback
     * to the user about the strength of their password.
     */
    public function passwordStrengthMeter(bool $passwordStrengthMeter = true): self
    {
        $this->strengthMeter = $passwordStrengthMeter;

        return $this;
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

    public function addMeterRelatedField(IFormField $input): self
    {
        $this->relatedFields[] = $input;

        return $this;
    }

    public function addMeterRelatedFieldId(string $fieldId): self
    {
        $this->relatedFieldsId[] = $fieldId;

        return $this;
    }

    public function getStrengthMeter(): bool
    {
        return $this->strengthMeter;
    }

    /**
     * @return string[]
     */
    public function getRelatedFieldsIDs(): array
    {
        $result = $this->relatedFieldsId;
        foreach ($this->relatedFields as $field) {
            $result[] = $field->getPrefixedId();
        }

        return $result;
    }
}
