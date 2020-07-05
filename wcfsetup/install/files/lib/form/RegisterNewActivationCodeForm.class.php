<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Shows the new activation code form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class RegisterNewActivationCodeForm extends AbstractForm {
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * password
	 * @var	string
	 */
	public $password = '';
	
	/**
	 * email
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * user object
	 * @var	User
	 */
	public $user = null;
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// username
		$this->validateUsername();
		
		// password
		$this->validatePassword();
		
		// email
		$this->validateEmail();
	}
	
	/**
	 * Validates the username.
	 */
	public function validateUsername() {
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		
		$this->user = User::getUserByUsername($this->username);
		if (!$this->user->userID) {
			throw new UserInputException('username', 'notFound');
		}
		
		if ($this->user->isEmailConfirmed()) {
			throw new UserInputException('username', 'alreadyEnabled');
		}
		
		if (!empty($this->user->getBlacklistMatches())) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validates the password.
	 */
	public function validatePassword() {
		if (empty($this->password)) {
			throw new UserInputException('password');
		}
		
		// check password
		if (!$this->user->checkPassword($this->password)) {
			throw new UserInputException('password', 'false');
		}
	}
	
	/**
	 * Validates the email address.
	 */
	public function validateEmail() {
		if (!empty($this->email)) {
			// check whether user entered the same email, instead of leaving the input empty
			if (mb_strtolower($this->email) != mb_strtolower($this->user->email)) {
				if (!UserRegistrationUtil::isValidEmail($this->email)) {
					throw new UserInputException('email', 'invalid');
				}
				
				// Check if email exists already.
				if (!UserUtil::isAvailableEmail($this->email)) {
					throw new UserInputException('email', 'notUnique');
				}
			}
			else {
				$this->email = '';
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// generate activation code
		$activationCode = UserRegistrationUtil::getActivationCode();
		
		// save user
		$parameters = ['activationCode' => $activationCode];
		if (!empty($this->email)) $parameters['email'] = $this->email;
		$this->objectAction = new UserAction([$this->user], 'update', [
			'data' => array_merge($this->additionalFields, $parameters)
		]);
		$this->objectAction->executeAction();
		
		// reload user to reflect changes
		$this->user = new User($this->user->userID);
		
		// send activation mail
		$email = new Email();
		$email->addRecipient(new UserMailbox($this->user));
		$email->setSubject(WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject'));
		$email->setBody(new MimePartFacade([
			new RecipientAwareTextMimePart('text/html', 'email_registerNeedActivation'),
			new RecipientAwareTextMimePart('text/plain', 'email_registerNeedActivation')
		]));
		$email->send();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.user.newActivationCode.success', ['email' => !empty($this->email) ? $this->email : $this->user->email]), 10);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST) && WCF::getUser()->userID) {
			$this->username = WCF::getUser()->username;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'username' => $this->username,
			'password' => $this->password,
			'email' => $this->email
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		if (!(REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER)) {
			throw new IllegalLinkException();
		}
		
		if ($this->user === null && !empty(WCF::getUser()->getBlacklistMatches())) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
