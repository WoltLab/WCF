<?php

namespace wcf\form;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\FormDocument;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Shows the new password form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class NewPasswordForm extends AbstractFormBuilderForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    public int $userID;
    public string $lostPasswordKey;
    public User $user;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['id']) && isset($_GET['k'])) {
            $this->userID = \intval($_GET['id']);
            $this->lostPasswordKey = StringUtil::trim($_GET['k']);
            if (!$this->userID || !$this->lostPasswordKey) {
                throw new IllegalLinkException();
            }

            $this->user = new User($this->userID);
            if (!$this->user->userID) {
                throw new IllegalLinkException();
            }

            if (!$this->user->lostPasswordKey) {
                $this->throwInvalidLinkException();
            }
            if (!\hash_equals($this->user->lostPasswordKey, $this->lostPasswordKey)) {
                $this->throwInvalidLinkException();
            }
            // expire lost password requests after a day
            if ($this->user->lastLostPasswordRequestTime < TIME_NOW - 86400) {
                $this->throwInvalidLinkException();
            }

            WCF::getSession()->register('lostPasswordRequest', [
                'userID' => $this->user->userID,
                'key' => $this->user->lostPasswordKey,
            ]);
        } else {
            if (!\is_array(WCF::getSession()->getVar('lostPasswordRequest'))) {
                throw new PermissionDeniedException();
            }
            $this->userID = \intval(WCF::getSession()->getVar('lostPasswordRequest')['userID']);

            $this->user = new User($this->userID);
            if (!$this->user->userID) {
                throw new IllegalLinkException();
            }
            if (!\hash_equals($this->user->lostPasswordKey, WCF::getSession()->getVar('lostPasswordRequest')['key'])) {
                $this->throwInvalidLinkException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        // We have to create the form manually here to avoid the form getting the ID 'newPassword'.
        $this->form = FormDocument::create('newPasswordForm');

        $this->form->appendChild(
            FormContainer::create('data')
                ->appendChildren([
                    PasswordFormField::create('newPassword')
                        ->label('wcf.user.newPassword')
                        ->required()
                        ->autoFocus()
                        ->removeFieldClass('medium')
                        ->addFieldClass('long')
                        ->autocomplete('new-password')
                        ->fieldAttribute('passwordrules', UserRegistrationUtil::getPasswordRulesAttributeValue())
                        ->addValidator(new FormFieldValidator(
                            'passwordValidator',
                            $this->validatePassword(...)
                        )),
                ])
        );
    }

    private function validatePassword(PasswordFormField $formField): void
    {
        if (isset($_POST['newPassword_passwordStrengthVerdict'])) {
            try {
                $newPasswordStrengthVerdict = JSON::decode($_POST['newPassword_passwordStrengthVerdict']);
            } catch (SystemException $e) {
                // ignore
            }
        }

        if (($newPasswordStrengthVerdict['score'] ?? 4) < PASSWORD_MIN_SCORE) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'notSecure',
                    'wcf.user.newPassword.error.notSecure'
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'user' => $this->user,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        WCF::getSession()->unregister('lostPasswordRequest');
        $this->updateUser();
        $this->saved();
        $this->forwardToIndexPage();

        exit;
    }

    private function updateUser(): void
    {
        $formData = $this->form->getData()['data'];
        $this->objectAction = new UserAction([$this->user], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'password' => $formData['newPassword'],
                'lastLostPasswordRequestTime' => 0,
                'lostPasswordKey' => '',
            ]),
        ]);
        $this->objectAction->executeAction();
    }

    private function forwardToIndexPage(): void
    {
        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable('wcf.user.newPassword.success', ['user' => $this->user]),
            10,
            'success',
            true
        );

        exit;
    }

    private function throwInvalidLinkException(): void
    {
        throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.newPassword.error.invalidLink'));
    }
}
