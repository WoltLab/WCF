<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
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
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
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
		
		if ($this->user->activationCode == 0) {
			throw new UserInputException('username', 'alreadyEnabled');
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
			if (!UserRegistrationUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'notValid');
			}
			
			// Check if email exists already.
			if (!UserUtil::isAvailableEmail($this->email)) {
				throw new UserInputException('email', 'notUnique');
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// generate activation code
		$activationCode = UserRegistrationUtil::getActivationCode();
		
		// save user
		$parameters = array('activationCode' => $activationCode);
		if (!empty($this->email)) $parameters['email'] = $this->email;
		$this->objectAction = new UserAction(array($this->user), 'update', array(
			'data' => array_merge($this->additionalFields, $parameters)
		));
		$this->objectAction->executeAction();
		
		// reload user to reflect changes
		$this->user = new User($this->user->userID);
		
		// send activation mail
		$mail = new Mail(array($this->user->username => (!empty($this->email) ? $this->email : $this->user->email)), WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject'), WCF::getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail', array('user' => $this->user)));
		$mail->send();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.user.newActivationCode.success', array('email' => (!empty($this->email) ? $this->email : $this->user->email))), 10);
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST) && WCF::getUser()->userID) {
			$this->username = WCF::getUser()->username;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'password' => $this->password,
			'email' => $this->email
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		if (REGISTER_ACTIVATION_METHOD != 1) {
			throw new IllegalLinkException();
		}
		
		parent::show();
	}
}
