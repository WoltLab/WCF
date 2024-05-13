<?php

namespace wcf\form;

use ParagonIE\ConstantTime\Hex;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\CaptchaFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\configuration\UserAuthenticationConfigurationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\UserUtil;

/**
 * Shows the lost password form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class LostPasswordForm extends AbstractFormBuilderForm
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    private const ALLOWED_RESETS_PER_24H = 5;

    public User $user;

    #[\Override]
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!UserAuthenticationConfigurationFactory::getInstance()->getConfigration()->canChangePassword) {
            return new IllegalLinkException();
        }
    }

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
                        ->label('wcf.user.usernameOrEmail')
                        ->required()
                        ->autoFocus()
                        ->maximumLength(255)
                        ->addValidator(new FormFieldValidator(
                            'usernameValidator',
                            $this->validateUsername(...)
                        )),
                    CaptchaFormField::create()
                        ->available(LOST_PASSWORD_USE_CAPTCHA)
                        ->objectType(CAPTCHA_TYPE)
                ])
        );
    }

    private function validateUsername(TextFormField $formField): void
    {
        $value = $formField->getValue();
        $this->user = User::getUserByUsername($value);
        if (!$this->user->userID) {
            $this->user = User::getUserByEmail($value);
        }

        if (!$this->user->userID) {
            if (UserUtil::isValidEmail($value)) {
                $formField->addValidationError(
                    new FormFieldValidationError(
                        'notFound',
                        'wcf.user.lostPassword.email.error.notFound',
                        [
                            'email' => $value,
                        ]
                    )
                );
            } else {
                $formField->addValidationError(
                    new FormFieldValidationError(
                        'notFound',
                        'wcf.user.username.error.notFound',
                        [
                            'username' => $value,
                        ]
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $requests = FloodControl::getInstance()->countContent(
            'com.woltlab.wcf.lostPasswordForm',
            new \DateInterval('PT24H')
        );
        if ($requests['count'] >= self::ALLOWED_RESETS_PER_24H) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.error.flood'));
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        // check if using 3rd party
        if ($this->user->authData) {
            HeaderUtil::delayedRedirect(
                LinkHandler::getInstance()->getLink(\ucfirst($this->user->getAuthProvider()) . 'Auth'),
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.user.username.error.3rdParty.redirect',
                    [
                        'provider' => WCF::getLanguage()->get('wcf.user.3rdparty.' . $this->user->getAuthProvider()),
                    ]
                ),
                10,
                'info',
                true
            );

            exit;
        }

        // check whether a lost password request was sent in the last 24 hours
        if ($this->user->lastLostPasswordRequestTime && TIME_NOW - 86400 < $this->user->lastLostPasswordRequestTime) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.lostPassword.error.tooManyRequests',
                ['hours' => \ceil(($this->user->lastLostPasswordRequestTime - (TIME_NOW - 86400)) / 3600)]
            ));
        }

        // generate a new lost password key
        $lostPasswordKey = Hex::encode(\random_bytes(20));

        // save key and request time in database
        $this->objectAction = new UserAction([$this->user], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'lostPasswordKey' => $lostPasswordKey,
                'lastLostPasswordRequestTime' => TIME_NOW,
            ]),
        ]);
        $this->objectAction->executeAction();

        // reload object
        $this->user = new User($this->user->userID);

        $email = new Email();
        $email->addRecipient(new UserMailbox($this->user));
        $email->setSubject($this->user->getLanguage()->getDynamicVariable('wcf.user.lostPassword.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_lostPassword'),
            new RecipientAwareTextMimePart('text/plain', 'email_lostPassword'),
        ]));
        $email->send();

        $this->saved();

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.lostPasswordForm');

        // forward to index page
        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.mail.sent'),
            10,
            'success',
            true
        );

        exit;
    }
}
