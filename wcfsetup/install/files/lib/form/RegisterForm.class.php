<?php
namespace wcf\form;
use wcf\acp\form\UserAddForm;
use wcf\data\object\type\ObjectType;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Shows the user registration form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * min number of seconds between form request and submit
	 * @var	integer
	 */
	public static $minRegistrationTime = 10;
	
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
			'randomFieldNames' => $this->randomFieldNames
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
			throw new UserInputException('username', 'notValid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validatePassword($password, $confirmPassword) {
		if (!$this->isExternalAuthentication) {
			parent::validatePassword($password, $confirmPassword);
			
			// check security of the given password
			if (!UserRegistrationUtil::isSecurePassword($password)) {
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
			throw new UserInputException('email', 'notValid');
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
						$this->additionalFields['authData'] = 'twitter:'.$twitterData['user_id'];
						
						WCF::getSession()->unregister('__twitterData');
						
						if (isset($twitterData['description']) && User::getUserOptionID('aboutMe') !== null) $saveOptions[User::getUserOptionID('aboutMe')] = $twitterData['description'];
						if (isset($twitterData['location']) && User::getUserOptionID('location') !== null) $saveOptions[User::getUserOptionID('location')] = $twitterData['location'];
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
						if (isset($facebookData['bio']) && User::getUserOptionID('bio') !== null) $saveOptions[User::getUserOptionID('aboutMe')] = $facebookData['bio'];
						if (isset($facebookData['location']) && User::getUserOptionID('location') !== null) $saveOptions[User::getUserOptionID('location')] = $facebookData['location']['name'];
						if (isset($facebookData['website']) && User::getUserOptionID('website') !== null) {
							$urls = preg_split('/[\s,;]/', $facebookData['website'], -1, PREG_SPLIT_NO_EMPTY);
							if (!empty($urls)) {
								if (!Regex::compile('^https?://')->match($urls[0])) {
									$urls[0] = 'http://' . $urls[0];
								}
								
								$saveOptions[User::getUserOptionID('homepage')] = $urls[0];
							}
						}
						
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
						$this->additionalFields['authData'] = 'google:'.$googleData['id'];
						
						WCF::getSession()->unregister('__googleData');
						
						if (isset($googleData['emails'][0]['value']) && $googleData['emails'][0]['value'] == $this->email) {
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
						if (isset($googleData['birthday']) && User::getUserOptionID('birthday') !== null) $saveOptions[User::getUserOptionID('birthday')] = $googleData['birthday'];
						if (isset($googleData['placesLived']) && User::getUserOptionID('location') !== null) {
							// save primary location
							$saveOptions[User::getUserOptionID('location')] = current(array_map(
								function ($element) { return $element['value']; },
								array_filter($googleData['placesLived'], function ($element) { return isset($element['primary']) && $element['primary']; })
							));
						}
						
						// avatar
						if (isset($googleData['image']['url'])) {
							$avatarURL = $googleData['image']['url'];
						}
					}
				break;
			}
			
			// create fake password
			$this->password = StringUtil::getRandomID();
		}
		
		$this->additionalFields['languageID'] = $this->languageID;
		if (LOG_IP_ADDRESS) $this->additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;
		
		// generate activation code
		$addDefaultGroups = true;
		if ((REGISTER_ACTIVATION_METHOD == 1 && !$registerVia3rdParty) || REGISTER_ACTIVATION_METHOD == 2) {
			$activationCode = UserRegistrationUtil::getActivationCode();
			$this->additionalFields['activationCode'] = $activationCode;
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
				'password' => $this->password
			]),
			'groups' => $this->groupIDs,
			'languageIDs' => $this->visibleLanguages,
			'options' => $saveOptions,
			'addDefaultGroups' => $addDefaultGroups
		];
		$this->objectAction = new UserAction([], 'create', $data);
		$result = $this->objectAction->executeAction();
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
		if (REGISTER_ACTIVATION_METHOD == 0) {
			$this->message = 'wcf.user.register.success';
		}
		else if (REGISTER_ACTIVATION_METHOD == 1) {
			// registering via 3rdParty leads to instant activation
			if ($registerVia3rdParty) {
				$this->message = 'wcf.user.register.success';
			}
			else {
				$mail = new Mail([$this->username => $this->email],
					WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject'),
					WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail', ['user' => $user])
				);
				$mail->send();
				$this->message = 'wcf.user.register.needActivation';
			}
		}
		else if (REGISTER_ACTIVATION_METHOD == 2) {
			$this->message = 'wcf.user.register.awaitActivation';
		}
		
		// notify admin
		if (REGISTER_ADMIN_NOTIFICATION) {
			// get default language
			$language = LanguageFactory::getInstance()->getLanguage(LanguageFactory::getInstance()->getDefaultLanguageID());
			
			// send mail
			$mail = new Mail(MAIL_ADMIN_ADDRESS, 
				$language->getDynamicVariable('wcf.user.register.notification.mail.subject'),
				$language->getDynamicVariable('wcf.user.register.notification.mail', ['user' => $user])
			);
			$mail->setLanguage($language);
			$mail->send();
		}
		
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
}
