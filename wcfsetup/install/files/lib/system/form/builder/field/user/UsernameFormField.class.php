<?php

namespace wcf\system\form\builder\field\user;

use wcf\data\user\User;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAttributeFormField;
use wcf\system\form\builder\field\IAutoCompleteFormField;
use wcf\system\form\builder\field\IAutoFocusFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\IMaximumLengthFormField;
use wcf\system\form\builder\field\IMinimumLengthFormField;
use wcf\system\form\builder\field\INullableFormField;
use wcf\system\form\builder\field\IPlaceholderFormField;
use wcf\system\form\builder\field\TAutoCompleteFormField;
use wcf\system\form\builder\field\TAutoFocusFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\TInputAttributeFormField;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\TNullableFormField;
use wcf\system\form\builder\field\TPlaceholderFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\util\UserUtil;

/**
 * Implementation of a form field to enter one non-existing username.
 *
 * The default id of fields of this class is `username` and the default label is `wcf.user.username`.
 *
 * Usernames have a minimum length of 3 characters and a maximum length of 100 characters by default.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class UsernameFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoCompleteFormField,
    IAutoFocusFormField,
    IImmutableFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField,
    INullableFormField,
    IPlaceholderFormField
{
    use TInputAttributeFormField;
    use TAutoCompleteFormField;
    use TAutoFocusFormField;
    use TDefaultIdFormField;
    use TImmutableFormField;
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TNullableFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_usernameFormField';

    /**
     * Creates a new instance of `UsernameFormField`.
     */
    public function __construct()
    {
        $this->label('wcf.user.username');
        $this->maximumLength(100);
        $this->minimumLength(3);
    }

    /**
     * @inheritDoc
     * @since       5.4
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return ['username'];
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if ($this->getValue() === null && !$this->isNullable()) {
            return '';
        }

        return parent::getSaveValue();
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
        if ($this->getValue() === '' || $this->getValue() === null) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } else {
            $this->validateMinimumLength($this->getValue());
            $this->validateMaximumLength($this->getValue());

            if (empty($this->getValidationErrors())) {
                if (!UserUtil::isValidUsername($this->getValue())) {
                    $this->addValidationError(new FormFieldValidationError(
                        'invalid',
                        'wcf.form.field.username.error.invalid'
                    ));
                } elseif (User::getUserByUsername($this->getValue())->userID) {
                    $this->addValidationError(new FormFieldValidationError(
                        'notUnique',
                        'wcf.form.field.username.error.notUnique'
                    ));
                }
            }
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'username';
    }
}
