<?php

namespace wcf\form;

use ParagonIE\ConstantTime\Hex;
use wcf\acp\form\UserAddForm;
use wcf\action\EmailValidationAction;
use wcf\action\UsernameValidationAction;
use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\data\object\type\ObjectType;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\configuration\UserAuthenticationConfigurationFactory;
use wcf\system\user\authentication\LoginRedirect;
use wcf\system\user\command\CreateRegistrationNotification;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Shows the user registration form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class RegisterForm extends UserAddForm
{
    /**
     * true if external authentication is used
     * @var bool
     */
    public $isExternalAuthentication = false;

    /**
     * @inheritDoc
     */
    public $neededPermissions = [];

    /**
     * holds a language variable with information about the registration process
     * e.g. if you need to activate your account
     * @var string
     */
    public $message = '';

    /**
     * captcha object type object
     * @var ObjectType
     */
    public $captchaObjectType;

    /**
     * name of the captcha object type; if empty, captcha is disabled
     * @var string
     */
    public $captchaObjectTypeName = CAPTCHA_TYPE;

    /**
     * true if captcha is used
     * @var bool
     */
    public $useCaptcha = REGISTER_USE_CAPTCHA;

    /**
     * field names
     * @var array
     */
    public $randomFieldNames = [];

    /**
     * list of fields that have matches in the blacklist
     * @var string[]
     * @since 5.2
     */
    public $blacklistMatches = [];

    /**
     * min number of seconds between form request and submit
     * @var int
     */
    public static $minRegistrationTime = 10;

    /**
     * @var mixed[]
     */
    public $passwordStrengthVerdict = [];

    /**
     * @since 6.1
     */
    public bool $termsConfirmed = false;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['url'])) {
            LoginRedirect::setUrl(StringUtil::trim($_REQUEST['url']));
        }

        // user is already registered
        if (WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        // registration disabled
        if (!UserAuthenticationConfigurationFactory::getInstance()->getConfigration()->canRegister) {
            throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.disabled'));
        }

        if (WCF::getSession()->getVar('__3rdPartyProvider')) {
            $this->isExternalAuthentication = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (!empty($this->username) || !empty($this->email)) {
            throw new PermissionDeniedException();
        }

        $this->randomFieldNames = WCF::getSession()->getVar('registrationRandomFieldNames');
        if ($this->randomFieldNames === null) {
            throw new PermissionDeniedException();
        }

        if (isset($_POST[$this->randomFieldNames['username']])) {
            $this->username = StringUtil::trim($_POST[$this->randomFieldNames['username']]);
        }
        if (isset($_POST[$this->randomFieldNames['email']])) {
            $this->email = StringUtil::trim($_POST[$this->randomFieldNames['email']]);
        }
        if (isset($_POST[$this->randomFieldNames['password']])) {
            $this->password = $_POST[$this->randomFieldNames['password']];
        }
        if (isset($_POST[$this->randomFieldNames['password'] . '_passwordStrengthVerdict'])) {
            try {
                $this->passwordStrengthVerdict = JSON::decode(
                    $_POST[$this->randomFieldNames['password'] . '_passwordStrengthVerdict']
                );
            } catch (SystemException $e) {
                // ignore
            }
        }
        if (!empty($_POST['termsConfirmed'])) {
            $this->termsConfirmed = true;
        }

        $this->groupIDs = [];

        if ($this->captchaObjectType) {
            $this->captchaObjectType->getProcessor()->readFormParameters();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initOptionHandler()
    {
        \assert($this->optionHandler instanceof UserOptionHandler);
        $this->optionHandler->setInRegistration();
        parent::initOptionHandler();
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // validate captcha first
        $this->validateCaptcha();

        parent::validate();

        // validate registration time
        if (
            !$this->isExternalAuthentication
            && (
                !WCF::getSession()->getVar('registrationStartTime')
                || (TIME_NOW - WCF::getSession()->getVar('registrationStartTime')) < self::$minRegistrationTime
            )
        ) {
            throw new UserInputException('registrationStartTime', []);
        }

        if (BLACKLIST_SFS_ENABLE) {
            $this->blacklistMatches = BlacklistEntry::getMatches(
                $this->username,
                $this->email,
                UserUtil::getIpAddress()
            );
            if (!empty($this->blacklistMatches) && BLACKLIST_SFS_ACTION === 'block') {
                throw new NamedUserException(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.register.error.blacklistMatches')
                );
            }
        }

        if (REGISTER_ENABLE_DISCLAIMER && !$this->termsConfirmed) {
            $this->errorType['termsConfirmed'] = 'empty';
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        if ($this->useCaptcha && $this->captchaObjectTypeName) {
            $this->captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName($this->captchaObjectTypeName);
            if ($this->captchaObjectType === null) {
                throw new SystemException("Unknown captcha object type with id '" . $this->captchaObjectTypeName . "'");
            }

            if (!$this->captchaObjectType->getProcessor()->isAvailable()) {
                $this->captchaObjectType = null;
            }

            if (WCF::getSession()->getVar('noRegistrationCaptcha')) {
                $this->captchaObjectType = null;
            }
        }

        parent::readData();

        if (empty($_POST)) {
            $this->languageID = WCF::getLanguage()->languageID;

            if (WCF::getSession()->getVar('__username')) {
                $this->username = WCF::getSession()->getVar('__username');
            }
            if (WCF::getSession()->getVar('__email')) {
                $this->email = WCF::getSession()->getVar('__email');
            }

            WCF::getSession()->register('registrationStartTime', TIME_NOW);

            // generate random field names
            $this->randomFieldNames = [
                'username' => UserRegistrationUtil::getRandomFieldName('username'),
                'email' => UserRegistrationUtil::getRandomFieldName('email'),
                'password' => UserRegistrationUtil::getRandomFieldName('password'),
            ];

            WCF::getSession()->register('registrationRandomFieldNames', $this->randomFieldNames);
        }
    }

    /**
     * Reads option tree on page init.
     */
    protected function readOptionTree()
    {
        $this->optionTree = $this->optionHandler->getOptionTree('profile');
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'captchaObjectType' => $this->captchaObjectType,
            'isExternalAuthentication' => $this->isExternalAuthentication,
            'randomFieldNames' => $this->randomFieldNames,
            'passwordRulesAttributeValue' => UserRegistrationUtil::getPasswordRulesAttributeValue(),
            'usernameValidationEndpoint' => LinkHandler::getInstance()->getControllerLink(UsernameValidationAction::class),
            'emailValidationEndpoint' => LinkHandler::getInstance()->getControllerLink(EmailValidationAction::class),
            'termsConfirmed' => $this->termsConfirmed,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        AbstractForm::show();
    }

    /**
     * Validates the captcha.
     */
    protected function validateCaptcha()
    {
        if ($this->captchaObjectType) {
            try {
                $this->captchaObjectType->getProcessor()->validate();
            } catch (UserInputException $e) {
                $this->errorType[$e->getField()] = $e->getType();
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateUsername($username)
    {
        parent::validateUsername($username);

        // check for min-max length
        if (!UserRegistrationUtil::isValidUsername($username)) {
            throw new UserInputException('username', 'invalid');
        }
    }

    #[\Override]
    protected function validatePassword(
        #[\SensitiveParameter]
        string $password
    ): void {
        if (!$this->isExternalAuthentication) {
            parent::validatePassword($password);

            // check security of the given password
            if (($this->passwordStrengthVerdict['score'] ?? 4) < PASSWORD_MIN_SCORE) {
                throw new UserInputException('password', 'notSecure');
            }
        }
    }

    #[\Override]
    protected function validateEmail(string $email): void
    {
        parent::validateEmail($email);

        if (!UserRegistrationUtil::isValidEmail($email)) {
            throw new UserInputException('email', 'invalid');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        // get options
        $saveOptions = $this->optionHandler->save();
        $registerVia3rdParty = false;

        if ($this->isExternalAuthentication) {
            $provider = WCF::getSession()->getVar('__3rdPartyProvider');
            switch ($provider) {
                case 'github':
                case 'facebook':
                case 'google':
                case 'twitter':
                    if (($oauthUser = WCF::getSession()->getVar('__oauthUser'))) {
                        $this->additionalFields['authData'] = $provider . ':' . $oauthUser->getId();
                    }
                    break;
            }

            // Accounts connected to a 3rdParty login do not have passwords.
            $this->password = null;

            if (WCF::getSession()->getVar('__email') && WCF::getSession()->getVar('__email') == $this->email) {
                $registerVia3rdParty = true;
            }
        }

        $eventParameters = [
            'saveOptions' => $saveOptions,
            'registerVia3rdParty' => $registerVia3rdParty,
        ];
        EventHandler::getInstance()->fireAction($this, 'registerVia3rdParty', $eventParameters);
        $saveOptions = $eventParameters['saveOptions'];
        $registerVia3rdParty = $eventParameters['registerVia3rdParty'];

        $this->additionalFields['languageID'] = $this->languageID;
        if (LOG_IP_ADDRESS) {
            $this->additionalFields['registrationIpAddress'] = UserUtil::getIpAddress();
        }

        // generate activation code
        $addDefaultGroups = true;
        if (
            !empty($this->blacklistMatches)
            || (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER && !$registerVia3rdParty)
            || (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN)
        ) {
            $activationCode = UserRegistrationUtil::getActivationCode();
            $emailConfirmCode = Hex::encode(\random_bytes(20));
            $this->additionalFields['activationCode'] = $activationCode;
            $this->additionalFields['emailConfirmed'] = $emailConfirmCode;
            $addDefaultGroups = false;
            $this->groupIDs = UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::GUESTS]);
        }

        // create user
        $data = [
            'data' => \array_merge($this->additionalFields, [
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
                'blacklistMatches' => (!empty($this->blacklistMatches)) ? JSON::encode($this->blacklistMatches) : '',
                'signatureEnableHtml' => 1,
            ]),
            'groups' => $this->groupIDs,
            'languageIDs' => $this->visibleLanguages,
            'options' => $saveOptions,
            'addDefaultGroups' => $addDefaultGroups,
        ];
        $this->objectAction = new UserAction([], 'create', $data);
        $result = $this->objectAction->executeAction();
        /** @var User $user */
        $user = $result['returnValues'];

        // update session
        WCF::getSession()->changeUser($user);

        // activation management
        if (REGISTER_ACTIVATION_METHOD == User::REGISTER_ACTIVATION_NONE && empty($this->blacklistMatches)) {
            $this->message = 'wcf.user.register.success';

            UserGroupAssignmentHandler::getInstance()->checkUsers([$user->userID]);
        } elseif (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER && empty($this->blacklistMatches)) {
            // registering via 3rdParty leads to instant activation
            if ($registerVia3rdParty) {
                $this->message = 'wcf.user.register.success';
            } else {
                $email = new Email();
                $email->addRecipient(new UserMailbox(WCF::getUser()));
                $email->setSubject(
                    WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject')
                );
                $email->setBody(new MimePartFacade([
                    new RecipientAwareTextMimePart('text/html', 'email_registerNeedActivation'),
                    new RecipientAwareTextMimePart('text/plain', 'email_registerNeedActivation'),
                ]));
                $email->send();
                $this->message = 'wcf.user.register.success.needActivation';
            }
        } elseif (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN || !empty($this->blacklistMatches)) {
            $this->message = 'wcf.user.register.success.awaitActivation';
        }

        $command = new CreateRegistrationNotification($user);
        $command();

        if ($this->captchaObjectType) {
            $this->captchaObjectType->getProcessor()->reset();
        }

        if (WCF::getSession()->getVar('noRegistrationCaptcha')) {
            WCF::getSession()->unregister('noRegistrationCaptcha');
        }

        // login user
        WCF::getSession()->unregister('registrationRandomFieldNames');
        WCF::getSession()->unregister('registrationStartTime');
        $this->saved();

        // forward to index page
        HeaderUtil::delayedRedirect(
            LoginRedirect::getUrl(),
            WCF::getLanguage()->getDynamicVariable($this->message, ['user' => $user]),
            15,
            'success',
            true
        );

        exit;
    }
}
