<?php

namespace wcf\form;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\user\command\RegistrationNotification;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user activation form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class RegisterActivationForm extends AbstractFormBuilderForm
{
    public User $user;

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChild(
            FormContainer::create('data')
                ->appendChildren([
                    TextFormField::create('username')
                        ->label('wcf.user.username')
                        ->required()
                        ->autoFocus()
                        ->maximumLength(255)
                        ->addValidator(new FormFieldValidator(
                            'usernameValidator',
                            $this->validateUsername(...)
                        )),
                    TextFormField::create('activationCode')
                        ->label('wcf.user.activationCode')
                        ->description('<a href="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getControllerLink(RegisterNewActivationCodeForm::class)) . '">' . WCF::getLanguage()->get('wcf.user.newActivationCode') . '</a>')
                        ->required()
                        ->maximumLength(40)
                        ->addValidator(new FormFieldValidator(
                            'activationCodeValidator',
                            $this->validateActivationCode(...)
                        ))
                ])
        );
    }

    private function validateUsername(TextFormField $formField): void
    {
        $value = $formField->getValue();
        $this->user = User::getUserByUsername($value);

        if (!$this->user->userID) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'notFound',
                    'wcf.user.username.error.notFound',
                    [
                        'username' => $value,
                    ]
                )
            );
            return;
        }

        if ($this->user->isEmailConfirmed()) {
            throw new NamedUserException(
                WCF::getLanguage()->get('wcf.user.registerActivation.error.userAlreadyEnabled')
            );
        }

        if (!empty($this->user->getBlacklistMatches())) {
            throw new PermissionDeniedException();
        }
    }

    private function validateActivationCode(TextFormField $formField): void
    {
        if (!isset($this->user) || !$this->user->emailConfirmed) {
            return;
        }

        if (!\hash_equals($this->user->emailConfirmed, $formField->getValue())) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'invalid',
                    'wcf.user.activationCode.error.invalid'
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        if (!(REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER)) {
            throw new IllegalLinkException();
        }

        if (empty($_POST) && !empty($_GET['u']) && !empty($_GET['a'])) {
            $user = new User(\intval($_GET['u']));
            $_POST['username'] = $user->userID ? $user->username : '';
            $_POST['activationCode'] = $_GET['a'];
            $_REQUEST['t'] = WCF::getSession()->getSecurityToken();
        }

        if (!empty(WCF::getUser()->getBlacklistMatches())) {
            throw new PermissionDeniedException();
        }

        parent::show();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->objectAction = new UserAction([$this->user], 'confirmEmail', ['skipNotification' => true]);
        $this->objectAction->executeAction();
        $this->saved();

        // forward to index page
        if ($this->user->requiresAdminActivation()) {
            $redirectText = WCF::getLanguage()
                ->getDynamicVariable('wcf.user.registerActivation.success.awaitAdminActivation');
        } else {
            $redirectText = WCF::getLanguage()->getDynamicVariable('wcf.user.registerActivation.success');
        }

        // User must be reloaded to get the correct activation status.
        $command = new RegistrationNotification(new User($this->user->userID));
        $command();

        HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), $redirectText, 10, 'success', true);

        exit;
    }
}
