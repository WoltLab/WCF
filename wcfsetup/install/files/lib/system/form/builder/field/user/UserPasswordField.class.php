<?php

namespace wcf\system\form\builder\field\user;

use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAttributeFormField;
use wcf\system\form\builder\field\IAutoCompleteFormField;
use wcf\system\form\builder\field\IAutoFocusFormField;
use wcf\system\form\builder\field\IPlaceholderFormField;
use wcf\system\form\builder\field\TAutoCompleteFormField;
use wcf\system\form\builder\field\TAutoFocusFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TInputAttributeFormField;
use wcf\system\form\builder\field\TPlaceholderFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

/**
 * Implementation of a form field to enter the password of the active user.
 *
 * This field is only available for logged-in users not user third party providers for logging in.
 * This field uses the `wcf.user.password` language item as the default form field label and uses
 * `password` as the default node id.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 */
final class UserPasswordField extends AbstractFormField implements
    IAttributeFormField,
    IAutoCompleteFormField,
    IAutoFocusFormField,
    IPlaceholderFormField
{
    use TInputAttributeFormField;
    use TAutoCompleteFormField;
    use TAutoFocusFormField;
    use TDefaultIdFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_userPasswordFormField';

    public function __construct()
    {
        $this->label('wcf.user.password');
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'password';
    }

    /**
     * @return string[]
     * @since       5.4
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return ['current-password'];
    }

    #[\Override]
    public function isAvailable()
    {
        return !WCF::getUser()->isGuest() && !WCF::getUser()->authData && parent::isAvailable();
    }

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    #[\Override]
    public function validate()
    {
        if ($this->isRequired() && !$this->getValue()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } elseif ($this->getValue() && !WCF::getUser()->checkPassword($this->getValue())) {
            $this->addValidationError(
                new FormFieldValidationError(
                    'false',
                    'wcf.user.password.error.false'
                )
            );
        }

        parent::validate();
    }
}
