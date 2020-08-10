<?php
namespace wcf\form;
use wcf\acp\form\UserAddForm;
use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\data\object\type\ObjectType;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\PlainTextMimePart;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\UserMailbox;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\notification\object\UserRegistrationUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Shows the user registration form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class RegisterForm extends UserAddForm {
	/**
	 * true if external authentication is used
	 * @var	boolean
	 */
	public $isExternalAuthentication = false;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = [];
	
	/**
	 * holds a language variable with information about the registration process
	 * e.g. if you need to activate your account
	 * @var	string
	 */
	public $message = '';
	
	/**
	 * captcha object type object
	 * @var	ObjectType
	 */
	public $captchaObjectType;
	
	/**
	 * name of the captcha object type; if empty, captcha is disabled
	 * @var	string
	 */
	public $captchaObjectTypeName = CAPTCHA_TYPE;
	
	/**
	 * true if captcha is used
	 * @var	boolean
	 */
	public $useCaptcha = REGISTER_USE_CAPTCHA;
	
	/**
	 * field names
	 * @var	array
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
	 * @var	integer
	 */
	public static $minRegistrationTime = 10;
	
	/**
	 * @var mixed[]
	 */
	public $passwordStrengthVerdict = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// user is already registered
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// registration disabled
		if (REGISTER_DISABLED) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.disabled'));
		}
		
		// check disclaimer
		if (REGISTER_ENABLE_DISCLAIMER && !WCF::getSession()->getVar('disclaimerAccepted')) {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Disclaimer'));
			exit;
		}
		
		if (WCF::getSession()->getVar('__3rdPartyProvider')) {
			$this->isExternalAuthentication = true;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!empty($this->username) || !empty($this->email)) {
			throw new PermissionDeniedException();
		}
		
		$this->randomFieldNames = WCF::getSession()->getVar('registrationRandomFieldNames');
		if ($this->randomFieldNames === null) {
			throw new PermissionDeniedException();
		}
		
		if (isset($_POST[$this->randomFieldNames['username']])) $this->username = StringUtil::trim($_POST[$this->randomFieldNames['username']]);
		if (isset($_POST[$this->randomFieldNames['email']])) $this->email = StringUtil::trim($_POST[$this->randomFieldNames['email']]);
		if (isset($_POST[$this->randomFieldNames['confirmEmail']])) $this->confirmEmail = StringUtil::trim($_POST[$this->randomFieldNames['confirmEmail']]);
		if (isset($_POST[$this->randomFieldNames['password']])) $this->password = $_POST[$this->randomFieldNames['password']];
		if (isset($_POST[$this->randomFieldNames['password'].'_passwordStrengthVerdict'])) {
			try {
				$this->passwordStrengthVerdict = JSON::decode($_POST[$this->randomFieldNames['password'].'_passwordStrengthVerdict']);
			}
			catch (SystemException $e) {
				// ignore
			}
		}
		if (isset($_POST[$this->randomFieldNames['confirmPassword']])) $this->confirmPassword = $_POST[$this->randomFieldNames['confirmPassword']];
		
		$this->groupIDs = [];
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->readFormParameters();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initOptionHandler() {
		/** @noinspection PhpUndefinedMethodInspection */
		$this->optionHandler->setInRegistration();
		parent::initOptionHandler();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// validate captcha first
		$this->validateCaptcha();
		
		parent::validate();
		
		// validate registration time
		if (!$this->isExternalAuthentication && (!WCF::getSession()->getVar('registrationStartTime') || (TIME_NOW - WCF::getSession()->getVar('registrationStartTime')) < self::$minRegistrationTime)) {
			throw new UserInputException('registrationStartTime', []);
		}
		
		if (BLACKLIST_SFS_ENABLE) {
			$this->blacklistMatches = BlacklistEntry::getMatches($this->username, $this->email, UserUtil::getIpAddress());
			if (!empty($this->blacklistMatches) && BLACKLIST_SFS_ACTION === 'block') {
				throw new NamedUserException('wcf.user.register.error.blacklistMatches');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if ($this->useCaptcha && $this->captchaObjectTypeName) {
			$this->captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName($this->captchaObjectTypeName);
			if ($this->captchaObjectType === null) {
				throw new SystemException("Unknown captcha object type with id '".$this->captchaObjectTypeName."'");
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
				WCF::getSession()->unregister('__username');
			}
			if (WCF::getSession()->getVar('__email')) {
				$this->email = $this->confirmEmail = WCF::getSession()->getVar('__email');
				WCF::getSession()->unregister('__email');
			}
			
			WCF::getSession()->register('registrationStartTime', TIME_NOW);
			
			// generate random field names
			$this->randomFieldNames = [
				'username' => UserRegistrationUtil::getRandomFieldName('username'),
				'email' => UserRegistrationUtil::getRandomFieldName('email'),
				'confirmEmail' => UserRegistrationUtil::getRandomFieldName('confirmEmail'),
				'password' => UserRegistrationUtil::getRandomFieldName('password'),
				'confirmPassword' => UserRegistrationUtil::getRandomFieldName('confirmPassword')
			];
			
			WCF::getSession()->register('registrationRandomFieldNames', $this->randomFieldNames);
		}
	}
	
	/**
	 * Reads option tree on page init.
	 */
	protected function readOptionTree() {
		$this->optionTree = $this->optionHandler->getOptionTree('profile');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'captchaObjectType' => $this->captchaObjectType,
			'isExternalAuthentication' => $this->isExternalAuthentication,
			'randomFieldNames' => $this->randomFieldNames,
			'passwordRulesAttributeValue' => UserRegistrationUtil::getPasswordRulesAttributeValue()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		AbstractForm::show();
	}
	
	/**
	 * Validates the captcha.
	 */
	protected function validateCaptcha() {
		if ($this->captchaObjectType) {
			try {
				$this->captchaObjectType->getProcessor()->validate();
			}
			catch (UserInputException $e) {
				$this->errorType[$e->getField()] = $e->getType();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateUsername($username) {
		parent::validateUsername($username);
		
		// check for min-max length
		if (!UserRegistrationUtil::isValidUsername($username)) {
			throw new UserInputException('username', 'invalid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validatePassword($password, $confirmPassword) {
		if (!$this->isExternalAuthentication) {
			parent::validatePassword($password, $confirmPassword);
			
			// check security of the given password
			if (($this->passwordStrengthVerdict['score'] ?? 4) < PASSWORD_MIN_SCORE) {
				throw new UserInputException('password', 'notSecure');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateEmail($email, $confirmEmail) {
		parent::validateEmail($email, $confirmEmail);
		
		if (!UserRegistrationUtil::isValidEmail($email)) {
			throw new UserInputException('email', 'invalid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// get options
		$saveOptions = $this->optionHandler->save();
		$registerVia3rdParty = false;
		
		$avatarURL = '';
		if ($this->isExternalAuthentication) {
			switch (WCF::getSession()->getVar('__3rdPartyProvider')) {
				case 'github':
					// GitHub
					if (WCF::getSession()->getVar('__githubData')) {
						$githubData = WCF::getSession()->getVar('__githubData');
						
						$this->additionalFields['authData'] = 'github:'.$githubData['id'];
						
						WCF::getSession()->unregister('__githubData');
						WCF::getSession()->unregister('__githubToken');
						
						if (WCF::getSession()->getVar('__email') && WCF::getSession()->getVar('__email') == $this->email) {
							$registerVia3rdParty = true;
						}
						
						if (isset($githubData['bio']) && User::getUserOptionID('aboutMe') !== null) $saveOptions[User::getUserOptionID('aboutMe')] = $githubData['bio'];
						if (isset($githubData['location']) && User::getUserOptionID('location') !== null) $saveOptions[User::getUserOptionID('location')] = $githubData['location'];
					}
				break;
				case 'twitter':
					// Twitter
					if (WCF::getSession()->getVar('__twitterData')) {
						$twitterData = WCF::getSession()->getVar('__twitterData');
						$this->additionalFields['authData'] = 'twitter:'.(isset($twitterData['id']) ? $twitterData['id'] : $twitterData['user_id']);
						
						WCF::getSession()->unregister('__twitterData');
						
						if (WCF::getSession()->getVar('__email') && WCF::getSession()->getVar('__email') == $this->email) {
							$registerVia3rdParty = true;
						}
						
						if (isset($twitterData['description']) && User::getUserOptionID('aboutMe') !== null) $saveOptions[User::getUserOptionID('aboutMe')] = $twitterData['description'];
						if (isset($twitterData['location']) && User::getUserOptionID('location') !== null) $saveOptions[User::getUserOptionID('location')] = $twitterData['location'];
						
						// avatar
						if (isset($twitterData['profile_image_url'])) {
							$avatarURL = $twitterData['profile_image_url'];
						}
					}
				break;
				case 'facebook':
					// Facebook
					if (WCF::getSession()->getVar('__facebookData')) {
						$facebookData = WCF::getSession()->getVar('__facebookData');
						$this->additionalFields['authData'] = 'facebook:'.$facebookData['id'];
						
						WCF::getSession()->unregister('__facebookData');
						
						if (isset($facebookData['email']) && $facebookData['email'] == $this->email) {
							$registerVia3rdParty = true;
						}
						
						if (isset($facebookData['gender']) && User::getUserOptionID('gender') !== null) $saveOptions[User::getUserOptionID('gender')] = ($facebookData['gender'] == 'male' ? UserProfile::GENDER_MALE : UserProfile::GENDER_FEMALE);
						
						if (isset($facebookData['birthday']) && User::getUserOptionID('birthday') !== null) {
							list($month, $day, $year) = explode('/', $facebookData['birthday']);
							$saveOptions[User::getUserOptionID('birthday')] = $year.'-'.$month.'-'.$day;
						}
						if (isset($facebookData['location']) && User::getUserOptionID('location') !== null) $saveOptions[User::getUserOptionID('location')] = $facebookData['location']['name'];
						
						// avatar
						if (isset($facebookData['picture']) && !$facebookData['picture']['data']['is_silhouette']) {
							$avatarURL = $facebookData['picture']['data']['url'];
						}
					}
				break;
				case 'google':
					// Google Plus
					if (WCF::getSession()->getVar('__googleData')) {
						$googleData = WCF::getSession()->getVar('__googleData');
						$this->additionalFields['authData'] = 'google:'.$googleData['sub'];
						
						WCF::getSession()->unregister('__googleData');
						
						if (isset($googleData['email']) && $googleData['email'] == $this->email) {
							$registerVia3rdParty = true;
						}
						
						if (isset($googleData['gender']) && User::getUserOptionID('gender') !== null) {
							switch ($googleData['gender']) {
								case 'male':
									$saveOptions[User::getUserOptionID('gender')] = UserProfile::GENDER_MALE;
								break;
								case 'female':
									$saveOptions[User::getUserOptionID('gender')] = UserProfile::GENDER_FEMALE;
								break;
							}
						}
						
						// avatar
						if (isset($googleData['picture'])) {
							$avatarURL = $googleData['picture'];
						}
					}
				break;
			}
			
			// create fake password
			$this->password = bin2hex(\random_bytes(20));
		}

		$eventParameters = [
			'saveOptions' => $saveOptions,
			'registerVia3rdParty' => $registerVia3rdParty,
		];
		EventHandler::getInstance()->fireAction($this, 'registerVia3rdParty', $eventParameters);
		$saveOptions = $eventParameters['saveOptions'];
		$registerVia3rdParty = $eventParameters['registerVia3rdParty'];
		
		$this->additionalFields['languageID'] = $this->languageID;
		if (LOG_IP_ADDRESS) $this->additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;
		
		// generate activation code
		$addDefaultGroups = true;
		if (!empty($this->blacklistMatches) || (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER && !$registerVia3rdParty) || (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN)) {
			$activationCode = UserRegistrationUtil::getActivationCode();
			$emailConfirmCode = bin2hex(\random_bytes(20));
			$this->additionalFields['activationCode'] = $activationCode;
			$this->additionalFields['emailConfirmed'] = $emailConfirmCode;
			$addDefaultGroups = false;
			$this->groupIDs = UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::GUESTS]);
		}
		
		// check gravatar support
		if (MODULE_GRAVATAR && Gravatar::test($this->email)) {
			$this->additionalFields['enableGravatar'] = 1;
		}
		
		// create user
		$data = [
			'data' => array_merge($this->additionalFields, [
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
				'blacklistMatches' => (!empty($this->blacklistMatches)) ? JSON::encode($this->blacklistMatches) : '',
				'signatureEnableHtml' => 1,
			]),
			'groups' => $this->groupIDs,
			'languageIDs' => $this->visibleLanguages,
			'options' => $saveOptions,
			'addDefaultGroups' => $addDefaultGroups
		];
		$this->objectAction = new UserAction([], 'create', $data);
		$result = $this->objectAction->executeAction();
		/** @var User $user */
		$user = $result['returnValues'];
		$userEditor = new UserEditor($user);
		
		// update session
		WCF::getSession()->changeUser($user);
		
		// set avatar if provided
		if (!empty($avatarURL)) {
			$userAvatarAction = new UserAvatarAction([], 'fetchRemoteAvatar', [
				'url' => $avatarURL,
				'userEditor' => $userEditor
			]);
			$userAvatarAction->executeAction();
		}
		
		// activation management
		if (REGISTER_ACTIVATION_METHOD == User::REGISTER_ACTIVATION_NONE && empty($this->blacklistMatches)) {
			$this->message = 'wcf.user.register.success';
			
			UserGroupAssignmentHandler::getInstance()->checkUsers([$user->userID]);
		}
		else if (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER && empty($this->blacklistMatches)) {
			// registering via 3rdParty leads to instant activation
			if ($registerVia3rdParty) {
				$this->message = 'wcf.user.register.success';
			}
			else {
				$email = new Email();
				$email->addRecipient(new UserMailbox(WCF::getUser()));
				$email->setSubject(WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject'));
				$email->setBody(new MimePartFacade([
					new RecipientAwareTextMimePart('text/html', 'email_registerNeedActivation'),
					new RecipientAwareTextMimePart('text/plain', 'email_registerNeedActivation')
				]));
				$email->send();
				$this->message = 'wcf.user.register.success.needActivation';
			}
		}
		else if (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN || !empty($this->blacklistMatches)) {
			$this->message = 'wcf.user.register.success.awaitActivation';
		}
		
		// notify admin
		if (REGISTER_ADMIN_NOTIFICATION) {
			// get default language
			$language = LanguageFactory::getInstance()->getDefaultLanguage();
			
			$email = new Email();
			$email->addRecipient(new Mailbox(MAIL_ADMIN_ADDRESS, null, $language));
			$email->setSubject($language->getDynamicVariable('wcf.user.register.notification.mail.subject'));
			$email->setBody(new PlainTextMimePart($language->getDynamicVariable('wcf.user.register.notification.mail', ['user' => $user])));
			$email->send();
		}
		
		$this->fireNotificationEvent($user);
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->reset();
		}
		
		if (WCF::getSession()->getVar('noRegistrationCaptcha')) {
			WCF::getSession()->unregister('noRegistrationCaptcha');
		}
		
		// login user
		UserAuthenticationFactory::getInstance()->getUserAuthentication()->storeAccessData($user, $this->username, $this->password);
		WCF::getSession()->unregister('registrationRandomFieldNames');
		WCF::getSession()->unregister('registrationStartTime');
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable($this->message, ['user' => $user]), 15);
		exit;
	}
	
	/**
	 * @param       User $user
	 * @since       5.2
	 */
	protected function fireNotificationEvent(User $user) {
		$recipientIDs = $this->getRecipientsForNotificationEvent();
		if (!empty($recipientIDs)) {
			UserNotificationHandler::getInstance()->fireEvent(
				'registration',
				'com.woltlab.wcf.user.registration.notification',
				new UserRegistrationUserNotificationObject($user),
				$recipientIDs
			);
		}
	}
	
	/**
	 * @return      integer[]
	 * @since       5.2
	 */
	protected function getRecipientsForNotificationEvent() {
		$sql = "SELECT  userID
			FROM    wcf".WCF_N."_user_to_group
			WHERE   groupID IN (
					SELECT  groupID
					FROM    wcf".WCF_N."_user_group_option_value
					WHERE   optionID IN (
							SELECT  optionID
							FROM    wcf".WCF_N."_user_group_option
							WHERE   optionName = ?
						)
						AND optionValue = ?
				)";
		$statement = WCF::getDB()->prepareStatement($sql, 100);
		$statement->execute([
			'admin.user.canSearchUser',
			1
		]);
		return $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
}
