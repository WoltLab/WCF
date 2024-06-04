<?php

namespace wcf\form;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the email activation form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class EmailActivationForm extends AbstractFormBuilderForm
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
                    IntegerFormField::create('userID')
                        ->label('wcf.user.userID')
                        ->required()
                        ->autoFocus()
                        ->removeFieldClass('short')
                        ->addFieldClass('long')
                        ->addValidator(new FormFieldValidator(
                            'userIDValidator',
                            $this->validateUserID(...)
                        )),
                    IntegerFormField::create('activationCode')
                        ->label('wcf.user.activationCode')
                        ->description('<a href="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getControllerLink(EmailNewActivationCodeForm::class)) . '">' . WCF::getLanguage()->get('wcf.user.newActivationCode') . '</a>')
                        ->required()
                        ->removeFieldClass('short')
                        ->addFieldClass('long')
                        ->addValidator(new FormFieldValidator(
                            'activationCodeValidator',
                            $this->validateActivationCode(...)
                        ))
                ])
        );
    }

    private function validateUserID(IntegerFormField $formField): void
    {
        $this->user = new User($formField->getValue());
        if (!$this->user->userID) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'invalid',
                    'wcf.user.userID.error.invalid'
                )
            );
            return;
        }

        if ($this->user->reactivationCode == 0) {
            throw new NamedUserException(
                WCF::getLanguage()->get('wcf.user.registerActivation.error.userAlreadyEnabled')
            );
        }

        // Check whether the new email isn't unique anymore.
        if (User::getUserByEmail($this->user->newEmail)->userID) {
            throw new NamedUserException(
                WCF::getLanguage()->get('wcf.user.email.error.notUnique')
            );
        }
    }

    private function validateActivationCode(IntegerFormField $formField): void
    {
        if (!isset($this->user) || !$this->user->reactivationCode) {
            return;
        }

        if ($this->user->reactivationCode != $formField->getValue()) {
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
            $_POST['userID'] = $_GET['u'];
            $_POST['activationCode'] = $_GET['a'];
            $_REQUEST['t'] = WCF::getSession()->getSecurityToken();
        }

        parent::show();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $data = [
            'email' => $this->user->newEmail,
            'newEmail' => '',
            'reactivationCode' => 0,
        ];

        // enable new email
        $this->objectAction = new UserAction([$this->user], 'update', [
            'data' => \array_merge($this->additionalFields, $data),
        ]);
        $this->objectAction->executeAction();

        // confirm email
        if (!$this->user->isEmailConfirmed() && empty($this->user->blacklistMatches)) {
            $this->objectAction = new UserAction([$this->user], 'confirmEmail');
            $this->objectAction->executeAction();
        }
        $this->saved();

        // forward to index page
        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable('wcf.user.emailActivation.success'),
            10,
            'success',
            true
        );

        exit;
    }
}
