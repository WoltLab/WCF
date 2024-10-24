<?php

namespace wcf\form;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Shows the new email activation code form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class EmailNewActivationCodeForm extends AbstractFormBuilderForm
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
                        ->value(WCF::getUser()->userID ? WCF::getUser()->username : '')
                        ->addValidator(new FormFieldValidator(
                            'usernameValidator',
                            $this->validateUsername(...)
                        )),
                    PasswordFormField::create('password')
                        ->label('wcf.user.password')
                        ->required()
                        ->removeFieldClass('medium')
                        ->addFieldClass('long')
                        ->autocomplete('current-password')
                        ->addMeterRelatedFieldId('username')
                        ->addValidator(new FormFieldValidator(
                            'passwordValidator',
                            $this->validatePassword(...)
                        )),
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

        if ($this->user->reactivationCode == 0) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'userAlreadyEnabled',
                    'wcf.user.registerActivation.error.userAlreadyEnabled',
                )
            );
            return;
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
        $this->objectAction = new UserAction([$this->user], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'reactivationCode' => UserRegistrationUtil::getActivationCode(),
            ]),
        ]);
        $this->objectAction->executeAction();

        // Use user list to allow overriding of the fields without duplicating logic.
        $userList = new UserList();
        $userList->useQualifiedShorthand = false;
        $userList->sqlSelects .= ", user_table.*, newEmail AS email";
        $userList->getConditionBuilder()->add('user_table.userID = ?', [$this->user->userID]);
        $userList->readObjects();
        $this->user = $userList->getObjects()[$this->user->userID];
    }

    private function sendActivationMail(): void
    {
        $email = new Email();
        $email->addRecipient(new UserMailbox($this->user));
        $email->setSubject(
            $this->user->getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation.mail.subject')
        );
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_changeEmailNeedReactivation'),
            new RecipientAwareTextMimePart('text/plain', 'email_changeEmailNeedReactivation'),
        ]));
        $email->send();
    }

    private function forwardToIndexPage(): void
    {
        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation'),
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
