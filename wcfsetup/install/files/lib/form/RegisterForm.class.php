<?php

namespace wcf\form;

use ParagonIE\ConstantTime\Hex;
use wcf\action\EmailValidationAction;
use wcf\action\UsernameValidationAction;
use wcf\command\user\CreateRegistrationNotification;
use wcf\command\user\CreateUser;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\language\Language;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\user\RegistrationSpamChecking;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\CaptchaFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\user\UsernameFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\option\user\TUserOptionFormFields;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\configuration\UserAuthenticationConfigurationFactory;
use wcf\system\user\authentication\LoginRedirect;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\HtmlString;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Shows the user registration form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<User, UserBuilder>
 */
final class RegisterForm extends AbstractDatabaseObjectBuilderForm
{
    use TUserOptionFormFields {
        getUserOptionFormField as getDefaultUserOptionFormField;
    }

    /**
     * min number of seconds between form request and submit
     */
    public static int $minRegistrationTime = 3;

    /**
     * true if external authentication is used
     */
    public bool $isExternalAuthentication = false;

    /**
     * name of the language item with information about the registration process,
     * e.g. if the account needs to be activated
     */
    public string $message = '';

    /**
     * true if the user registers with the email address provided by the external authentication
     */
    private bool $registerVia3rdParty = false;

    /**
     * options that are asked during the registration grouped by the name of their category
     * @var array<string, UserOption[]>
     */
    private array $options = [];

    private RegistrationSpamChecking $spamCheckEvent;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        if (isset($_REQUEST['url']) && $_REQUEST['url'] !== '') {
            LoginRedirect::setUrl(StringUtil::trim($_REQUEST['url']));
        }

        // user is already registered
        if (!WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }

        // registration disabled
        if (!UserAuthenticationConfigurationFactory::getInstance()->getConfigration()->canRegister) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->get('wcf.user.register.error.disabled')
            ));
        }

        if (WCF::getSession()->getVar('__3rdPartyProvider') !== null) {
            $this->isExternalAuthentication = true;
        }

        $this->options = $this->getUserOptionsByCategory(
            'profile',
            $this->isAskedDuringRegistration(...)
        );

        if ($_POST === []) {
            WCF::getSession()->register('registrationStartTime', \TIME_NOW);
        }
    }

    /**
     * Returns true if the given option is asked during the registration.
     */
    private function isAskedDuringRegistration(UserOption $option): bool
    {
        if (!$option->validateOptions() || !$option->validatePermissions() || !$option->isEditable(true)) {
            return false;
        }

        return $option->askDuringRegistration !== 0
            || $option->required !== 0
            || ($option->editable & UserOption::EDITABILITY_OWNER_DURING_REGISTRATION) !== 0
            || $this->isBirthdayWithMinimumAge($option);
    }

    private function isBirthdayWithMinimumAge(UserOption $option): bool
    {
        return $option->optionName === 'birthday' && \REGISTER_MIN_USER_AGE !== 0;
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserBuilder
    {
        $builder = UserBuilder::forCreate()
            ->setRegistrationDate(\TIME_NOW)
            ->setLanguageID(WCF::getLanguage()->languageID)
            ->setSignatureEnableHtml(true);

        if (\LOG_IP_ADDRESS !== 0) {
            $builder->setRegistrationIpAddress(UserUtil::getIpAddress());
        }

        if ($this->isExternalAuthentication) {
            $provider = WCF::getSession()->getVar('__3rdPartyProvider');
            switch ($provider) {
                case 'github':
                case 'facebook':
                case 'google':
                case 'twitter':
                    if (($oauthUser = WCF::getSession()->getVar('__oauthUser')) !== null) {
                        $builder->setAuthData($provider . ':' . $oauthUser->getId());
                    }
                    break;
            }

            // Accounts connected to a 3rdParty login do not have passwords.
            $builder->setPassword(null);

            $email = WCF::getSession()->getVar('__email');
            if ($email !== null && $email === $this->form->getFormField('email')?->getSaveValue()) {
                $this->registerVia3rdParty = true;
            }
        }

        if ($this->spamCheckEvent->hasMatches()) {
            $builder->setBlacklistMatches($this->spamCheckEvent->getMatches());
        }

        if ($this->requiresActivation()) {
            $builder->setActivationCode(UserRegistrationUtil::getActivationCode());
            if (!$this->registerVia3rdParty) {
                $builder->setEmailConfirmed(Hex::encode(\random_bytes(20)));
            }
            $builder->setGroupIDs(UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::GUESTS]));
        }

        return $builder;
    }

    private function requiresActivation(): bool
    {
        return $this->spamCheckEvent->hasMatches()
            || (
                ((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) !== 0
                && !$this->registerVia3rdParty
            )
            || ((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN) !== 0;
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        return new CreateUser($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $this->form->appendChild($this->getGeneralFormContainer());
        $this->form->appendChildren($this->getUserOptionFormContainers($this->options));

        if (
            \REGISTER_USE_CAPTCHA !== 0
            && WCF::getSession()->getVar('noRegistrationCaptcha') !== true
        ) {
            $this->form->appendChild(
                CaptchaFormField::create()
                    ->objectType(\CAPTCHA_TYPE)
            );
        }
    }

    #[\Override]
    protected function finalizeForm(): void
    {
        parent::finalizeForm();

        $this->form->getButton('submitButton')->label('wcf.user.button.register');
    }

    private function getGeneralFormContainer(): FormContainer
    {
        $container = FormContainer::create('general')
            ->appendChildren([
                UsernameFormField::create()
                    ->required()
                    ->minimumLength(\REGISTER_USERNAME_MIN_LENGTH)
                    ->maximumLength(\REGISTER_USERNAME_MAX_LENGTH)
                    ->autoComplete('username')
                    ->fieldAttribute(
                        'data-validation-endpoint',
                        LinkHandler::getInstance()->getControllerLink(UsernameValidationAction::class)
                    )
                    ->description('wcf.user.username.description')
                    ->value(WCF::getSession()->getVar('__username') ?? '')
                    ->addValidator(new FormFieldValidator(
                        'registrationUtil',
                        static function (UsernameFormField $formField) {
                            if (!UserRegistrationUtil::isValidUsername((string)$formField->getValue())) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'invalid',
                                    'wcf.user.username.error.invalid'
                                ));
                            }
                        }
                    ))
                    ->saveValueCallback(static function (UserBuilder $builder, UsernameFormField $field) {
                        $builder->setUsername($field->getSaveValue());
                    }),
                EmailFormField::create('email')
                    ->label('wcf.user.email')
                    ->required()
                    ->autoComplete('email')
                    ->fieldAttribute(
                        'data-validation-endpoint',
                        LinkHandler::getInstance()->getControllerLink(EmailValidationAction::class)
                    )
                    ->value(WCF::getSession()->getVar('__email') ?? '')
                    ->addValidator(new FormFieldValidator(
                        'registrationUtil',
                        static function (EmailFormField $formField) {
                            $email = (string)$formField->getValue();
                            if (!UserRegistrationUtil::isValidEmail($email)) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'invalid',
                                    'wcf.user.email.error.invalid'
                                ));
                            } elseif (!User::getUserByEmail($email)->isGuest()) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.user.email.error.notUnique'
                                ));
                            }
                        }
                    ))
                    ->saveValueCallback(static function (UserBuilder $builder, EmailFormField $field) {
                        $builder->setEmail($field->getSaveValue());
                    }),
            ]);

        if (!$this->isExternalAuthentication) {
            $container->appendChild(
                PasswordFormField::create('password')
                    ->label('wcf.user.password')
                    ->description('wcf.user.password.description')
                    ->addClass('passwordFormField--stacked')
                    ->required()
                    ->autoComplete('new-password')
                    ->fieldAttribute('passwordrules', UserRegistrationUtil::getPasswordRulesAttributeValue())
                    ->passwordStrengthMeter()
                    ->addMeterRelatedFieldId('username')
                    ->addMeterRelatedFieldId('email')
                    ->addValidator(new FormFieldValidator('passwordStrength', $this->validatePasswordStrength(...)))
                    ->saveValueCallback(static function (UserBuilder $builder, PasswordFormField $field) {
                        $builder->setPassword($field->getSaveValue());
                    })
            );
        }

        $availableLanguages = LanguageFactory::getInstance()->getLanguages();
        if (\count($availableLanguages) > 1) {
            $availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();

            $container->appendChild(
                SelectFormField::create('languageID')
                    ->label('wcf.user.language')
                    ->description('wcf.user.language.description')
                    ->options(\array_map(
                        static fn(Language $language) => StringUtil::encodeHTML($language->languageName),
                        $availableLanguages
                    ))
                    ->required()
                    ->ignoreInvalidValues()
                    ->defaultValue(LanguageFactory::getInstance()->getDefaultLanguageID())
                    ->value(WCF::getLanguage()->languageID)
                    ->saveValueCallback(static function (UserBuilder $builder, SelectFormField $field) {
                        $builder->setLanguageID((int)$field->getSaveValue());
                    })
            );

            if ($availableContentLanguages !== []) {
                $container->appendChild(
                    MultipleSelectionFormField::create('visibleLanguages')
                        ->label('wcf.user.visibleLanguages')
                        ->description('wcf.user.visibleLanguages.description')
                        ->options(\array_map(
                            static fn(Language $language) => StringUtil::encodeHTML($language->languageName),
                            $availableContentLanguages
                        ))
                        ->ignoreInvalidValues()
                        ->saveValueCallback(
                            static function (UserBuilder $builder, MultipleSelectionFormField $field) use ($availableContentLanguages) {
                                $languageIDs = \array_map(\intval(...), $field->getValue() ?? []);
                                if ($languageIDs === []) {
                                    $languageID = (int)$field->getDocument()->getFormField('languageID')?->getSaveValue();
                                    if (isset($availableContentLanguages[$languageID])) {
                                        $languageIDs[] = $languageID;
                                    }
                                }

                                $builder->setLanguageIDs($languageIDs);
                            }
                        )
                );
            }
        }

        if (\REGISTER_ENABLE_DISCLAIMER !== 0) {
            $container->appendChild(
                BooleanFormField::create('termsConfirmed')
                    ->label('wcf.user.register.confirmTerms')
                    ->required()
            );
        }

        return $container;
    }

    /**
     * Returns the form field for the given user option, enforcing the minimum
     * age for the birthday.
     */
    protected function getUserOptionFormField(UserOption $option): AbstractFormField
    {
        $formField = $this->getDefaultUserOptionFormField($option);

        if ($this->isBirthdayWithMinimumAge($option)) {
            $formField->required();
            $formField->addValidator(new FormFieldValidator(
                'minimumAge',
                static function (IFormField $formField) {
                    $value = $formField->getSaveValue();
                    if ($value !== null && $value !== '' && DateUtil::getAge($value) < \REGISTER_MIN_USER_AGE) {
                        $formField->addValidationError(new FormFieldValidationError(
                            'birthdayTooYoung',
                            'wcf.user.option.error.birthdayTooYoung'
                        ));
                    }
                }
            ));
        }

        return $formField;
    }

    private function validatePasswordStrength(PasswordFormField $formField): void
    {
        $verdict = [];
        if (isset($_POST['password_passwordStrengthVerdict'])) {
            try {
                $verdict = \json_decode($_POST['password_passwordStrengthVerdict'], true, flags: \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                // ignore
            }
        }

        if (($verdict['score'] ?? 4) < \PASSWORD_MIN_SCORE) {
            $formField->addValidationError(new FormFieldValidationError(
                'notSecure',
                'wcf.user.password.error.notSecure'
            ));
        }
    }

    #[\Override]
    public function validate(): void
    {
        parent::validate();

        // validate registration time
        if (
            !$this->isExternalAuthentication
            && (
                WCF::getSession()->getVar('registrationStartTime') === null
                || (\TIME_NOW - WCF::getSession()->getVar('registrationStartTime')) < self::$minRegistrationTime
            )
        ) {
            $this->form->invalid();

            throw new UserInputException($this->form->getPrefixedId());
        }

        $this->spamCheckEvent = new RegistrationSpamChecking(
            $this->form->getFormField('username')->getSaveValue(),
            $this->form->getFormField('email')->getSaveValue(),
            UserUtil::getIpAddress()
        );
        EventHandler::getInstance()->fire($this->spamCheckEvent);
        if ($this->spamCheckEvent->hasMatches() && \REGISTER_ANTISPAM_ACTION === 'block') {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.user.register.error.blacklistMatches')
            ));
        }
    }

    #[\Override]
    public function saved(): void
    {
        $user = $this->object;
        \assert($user instanceof User);

        WCF::getSession()->changeUser($user);

        $this->message = $this->handleActivation($user);

        new CreateRegistrationNotification($user)();

        // The form is not rebuilt, because the user is redirected after the registration.
        AbstractForm::saved();
    }

    #[\Override]
    protected function afterSave(): void
    {
        parent::afterSave();

        $this->form->cleanup();
        WCF::getSession()->unregister('noRegistrationCaptcha');
        WCF::getSession()->unregister('registrationStartTime');

        HeaderUtil::delayedRedirect(
            LoginRedirect::getUrl(),
            WCF::getLanguage()->getDynamicVariable($this->message, ['user' => $this->object]),
            15,
            'success',
            true
        );

        exit;
    }

    /**
     * Handles the activation of the new user and returns the name of the
     * language item with the message that is shown to the user.
     */
    private function handleActivation(User $user): string
    {
        if ((int)\REGISTER_ACTIVATION_METHOD === User::REGISTER_ACTIVATION_NONE && !$this->spamCheckEvent->hasMatches()) {
            UserGroupAssignmentHandler::getInstance()->checkUsers([$user->userID]);

            return 'wcf.user.register.success';
        }

        if (
            ((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) !== 0
            && !$this->spamCheckEvent->hasMatches()
        ) {
            // registering via 3rdParty leads to instant activation
            if ($this->registerVia3rdParty) {
                if (((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN) !== 0) {
                    return 'wcf.user.register.success.awaitActivation';
                }

                return 'wcf.user.register.success';
            }

            $email = new Email();
            $email->addRecipient(new UserMailbox($user));
            $email->setSubject(
                WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject')
            );
            $email->setBody(new MimePartFacade([
                new RecipientAwareTextMimePart('text/html', 'email_registerNeedActivation'),
                new RecipientAwareTextMimePart('text/plain', 'email_registerNeedActivation'),
            ]));
            $email->send();

            return 'wcf.user.register.success.needActivation';
        }

        return 'wcf.user.register.success.awaitActivation';
    }

    #[\Override]
    protected function setFormAction(): void
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class));
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'isExternalAuthentication' => $this->isExternalAuthentication,
        ]);
    }
}
