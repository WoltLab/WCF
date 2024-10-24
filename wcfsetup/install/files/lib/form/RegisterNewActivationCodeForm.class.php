<?php

namespace wcf\form;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the new activation code form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class RegisterNewActivationCodeForm extends AbstractFormBuilderForm
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
                    PasswordFormField::create('password')
                        ->label('wcf.user.password')
                        ->required()
                        ->removeFieldClass('medium')
                        ->addFieldClass('long')
                        ->addMeterRelatedFieldId('username')
                        ->addMeterRelatedFieldId('email')
                        ->autocomplete('current-password')
                        ->addValidator(new FormFieldValidator(
                            'passwordValidator',
                            $this->validatePassword(...)
                        )),
                    EmailFormField::create('email')
                        ->label('wcf.user.email')
                        ->description('wcf.user.registerNewActivationCode.email.description')
                        ->addValidator(new FormFieldValidator(
                            'emailValidator',
                            $this->validateEmail(...)
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
            $formField->addValidationError(
                new FormFieldValidationError(
                    'userAlreadyEnabled',
                    'wcf.user.registerActivation.error.userAlreadyEnabled',
                )
            );
            return;
        }

        if (!empty($this->user->getBlacklistMatches())) {
            throw new PermissionDeniedException();
        }
    }

    private function validatePassword(PasswordFormField $formField): void
    {
        if (!$this->user->userID) {
            return;
        }

        if (!$this->user->checkPassword($formField->getValue())) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'false',
                    'wcf.user.password.error.false'
                )
            );
        }
    }

    private function validateEmail(EmailFormField $formField): void
    {
        if (!$this->user->userID) {
            return;
        }

        $value = $formField->getValue();
        if (!$value) {
            return;
        }

        if (\mb_strtolower($value) != \mb_strtolower($this->user->email)) {
            if (User::getUserByEmail($value)->userID) {
                $formField->addValidationError(
                    new FormFieldValidationError(
                        'notUnique',
                        'wcf.user.email.error.notUnique'
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->updateUser();
        $this->sendActivationMail();
        $this->saved();
        $this->forwardToIndexPage();
    }

    private function updateUser(): void
    {
        $formData = $this->form->getData()['data'];
        $parameters = ['emailConfirmed' => \bin2hex(\random_bytes(20))];
        if (!empty($formData['email'])) {
            $parameters['email'] = $formData['email'];
        }
        $this->objectAction = new UserAction([$this->user], 'update', [
            'data' => \array_merge($this->additionalFields, $parameters),
        ]);
        $this->objectAction->executeAction();

        // Reload user to reflect changes.
        $this->user = new User($this->user->userID);
    }

    private function sendActivationMail(): void
    {
        $email = new Email();
        $email->addRecipient(new UserMailbox($this->user));
        $email->setSubject(WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_registerNeedActivation'),
            new RecipientAwareTextMimePart('text/plain', 'email_registerNeedActivation'),
        ]));
        $email->send();
    }

    private function forwardToIndexPage(): void
    {
        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable(
                'wcf.user.newActivationCode.success',
                ['email' => $this->user->email]
            ),
            10,
            'success',
            true
        );

        exit;
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        if (!(REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER)) {
            throw new IllegalLinkException();
        }

        if (!empty(WCF::getUser()->getBlacklistMatches())) {
            throw new PermissionDeniedException();
        }

        parent::show();
    }
}
