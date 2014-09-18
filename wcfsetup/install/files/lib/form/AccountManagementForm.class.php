<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Shows the account management form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class AccountManagementForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * user data to update
	 * @var	array
	 */
	public $updateParameters = array();
	
	/**
	 * user options to update
	 * @var	array
	 */
	public $updateOptions = array();
	
	/**
	 * success messages
	 * @var	array
	 */
	public $success = array();
	
	/**
	 * user password
	 * @var	string
	 */
	public $password = '';
	
	/**
	 * new email address
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * confirmed new email address
	 * @var	string
	 */
	public $confirmEmail = '';
	
	/**
	 * new password
	 * @var	string
	 */
	public $newPassword = '';
	
	/**
	 * confirmed new password
	 * @var	string
	 */
	public $confirmNewPassword = '';
	
	/**
	 * new user name
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * indicates if the user quit
	 * @var	integer
	 */
	public $quit = 0;
	
	/**
	 * indicates if the user canceled their quit
	 * @var	integer
	 */
	public $cancelQuit = 0;
	
	/**
	 * timestamp at which the user quit
	 * @var	integer
	 */
	public $quitStarted = 0;
	
	/**
	 * indicates if the user wants to connect github
	 * @var	integer
	 */
	public $githubConnect = 0;
	
	/**
	 * indicates if the user wants to disconnect github
	 * @var	integer
	 */
	public $githubDisconnect = 0;
	
	/**
	 * indicates if the user wants to connect twitter
	 * @var	integer
	 */
	public $twitterConnect = 0;
	
	/**
	 * indicates if the user wants to disconnect twitter
	 * @var	integer
	 */
	public $twitterDisconnect = 0;
	
	/**
	 * indicates if the user wants to connect facebook
	 * @var	integer
	 */
	public $facebookConnect = 0;
	
	/**
	 * indicates if the user wants to disconnect facebook
	 * @var	integer
	 */
	public $facebookDisconnect = 0;
	
	/**
	 * indicates if the user wants to connect google
	 * @var	integer
	 */
	public $googleConnect = 0;
	
	/**
	 * indicates if the user wants to disconnect google
	 * @var	integer
	 */
	public $googleDisconnect = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->quitStarted = WCF::getUser()->quitStarted;
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['email'])) $this->email = $_POST['email'];
		if (isset($_POST['confirmEmail'])) $this->confirmEmail = $_POST['confirmEmail'];
		if (isset($_POST['newPassword'])) $this->newPassword = $_POST['newPassword'];
		if (isset($_POST['confirmNewPassword'])) $this->confirmNewPassword = $_POST['confirmNewPassword'];
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['quit'])) $this->quit = intval($_POST['quit']);
		if (isset($_POST['cancelQuit'])) $this->cancelQuit = intval($_POST['cancelQuit']);
		if (isset($_POST['githubDisconnect'])) $this->githubDisconnect = intval($_POST['githubDisconnect']);
		if (isset($_POST['twitterDisconnect'])) $this->twitterDisconnect = intval($_POST['twitterDisconnect']);
		if (isset($_POST['facebookDisconnect'])) $this->facebookDisconnect = intval($_POST['facebookDisconnect']);
		if (isset($_POST['googleDisconnect'])) $this->googleDisconnect = intval($_POST['googleDisconnect']);
		
		if (!WCF::getUser()->hasAdministrativeAccess()) {
			if (isset($_POST['facebookConnect'])) $this->facebookConnect = intval($_POST['facebookConnect']);
			if (isset($_POST['githubConnect'])) $this->githubConnect = intval($_POST['githubConnect']);
			if (isset($_POST['googleConnect'])) $this->googleConnect = intval($_POST['googleConnect']);
			if (isset($_POST['twitterConnect'])) $this->twitterConnect = intval($_POST['twitterConnect']);
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// password
		if (!WCF::getUser()->authData) {
			if (empty($this->password)) {
				throw new UserInputException('password');
			}
			
			if (!WCF::getUser()->checkPassword($this->password)) {
				throw new UserInputException('password', 'false');
			}
		}
		
		// user name
		if (WCF::getSession()->getPermission('user.profile.canRename') && $this->username != WCF::getUser()->username) {
			if (mb_strtolower($this->username) != mb_strtolower(WCF::getUser()->username)) {
				if (WCF::getUser()->lastUsernameChange + WCF::getSession()->getPermission('user.profile.renamePeriod') * 86400 > TIME_NOW) {
					throw new UserInputException('username', 'alreadyRenamed');
				}
				
				// checks for forbidden chars (e.g. the ",")
				if (!UserRegistrationUtil::isValidUsername($this->username)) {
					throw new UserInputException('username', 'notValid');
				}
				
				// checks if user name exists already.
				if (!UserUtil::isAvailableUsername($this->username)) {
					throw new UserInputException('username', 'notUnique');
				}
			}
		}
		
		// password
		if (!WCF::getUser()->authData) {
			if (!empty($this->newPassword) || !empty($this->confirmNewPassword)) {
				if (empty($this->newPassword)) {
					throw new UserInputException('newPassword');
				}
				
				if (empty($this->confirmNewPassword)) {
					throw new UserInputException('confirmNewPassword');
				}
				
				if (!UserRegistrationUtil::isSecurePassword($this->newPassword)) {
					throw new UserInputException('newPassword', 'notSecure');
				}
				
				if ($this->newPassword != $this->confirmNewPassword) {
					throw new UserInputException('confirmNewPassword', 'notEqual');
				}
			}
		}
		
		// email
		if (WCF::getSession()->getPermission('user.profile.canChangeEmail') && $this->email != WCF::getUser()->email && $this->email != WCF::getUser()->newEmail) {
			if (empty($this->email)) {	
				throw new UserInputException('email');
			}
			
			// checks if only letter case has changed
			if (mb_strtolower($this->email) != mb_strtolower(WCF::getUser()->email)) {
				// check for valid email (one @ etc.)
				if (!UserRegistrationUtil::isValidEmail($this->email)) {
					throw new UserInputException('email', 'notValid');
				}
				
				// checks if email already exists.
				if (!UserUtil::isAvailableEmail($this->email)) {
					throw new UserInputException('email', 'notUnique');
				}
			}
			
			// checks confirm input
			if (mb_strtolower($this->email) != mb_strtolower($this->confirmEmail)) {
				throw new UserInputException('confirmEmail', 'notEqual');
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (empty($_POST)) {
			$this->username = WCF::getUser()->username;
			$this->email = $this->confirmEmail = WCF::getUser()->email;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'password' => $this->password,
			'email' => $this->email,
			'confirmEmail' => $this->confirmEmail,
			'newPassword' => $this->newPassword,
			'confirmNewPassword' => $this->confirmNewPassword,
			'username' => $this->username,
			'renamePeriod' => WCF::getSession()->getPermission('user.profile.renamePeriod'),
			'quitStarted' => $this->quitStarted,
			'quit' => $this->quit,
			'cancelQuit' => $this->cancelQuit,
			'githubConnect' => $this->githubConnect,
			'githubDisconnect' => $this->githubDisconnect,
			'twitterConnect' => $this->twitterConnect,
			'twitterDisconnect' => $this->twitterDisconnect,
			'facebookConnect' => $this->facebookConnect,
			'facebookDisconnect' => $this->facebookDisconnect,
			'googleConnect' => $this->googleConnect,
			'googleDisconnect' => $this->googleDisconnect
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.accountManagement');
		
		parent::show();
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// quit
		if (WCF::getSession()->getPermission('user.profile.canQuit')) {
			if (!WCF::getUser()->quitStarted && $this->quit == 1) {
				$this->updateParameters['quitStarted'] = TIME_NOW;
				$this->quitStarted = TIME_NOW;
				$this->success[] = 'wcf.user.quit.success';
			}
			else if (WCF::getUser()->quitStarted && $this->cancelQuit == 1) {
				$this->updateParameters['quitStarted'] = 0;
				$this->quitStarted = 0;
				$this->success[] = 'wcf.user.quit.cancel.success';
			}
		}
		
		// user name
		if (WCF::getSession()->getPermission('user.profile.canRename') && $this->username != WCF::getUser()->username) {
			if (mb_strtolower($this->username) != mb_strtolower(WCF::getUser()->username)) {
				$this->updateParameters['lastUsernameChange'] = TIME_NOW;
				$this->updateParameters['oldUsername'] = WCF::getUser()->username;
			}
			$this->updateParameters['username'] = $this->username;
			$this->success[] = 'wcf.user.changeUsername.success';
		}
		
		// email
		if (WCF::getSession()->getPermission('user.profile.canChangeEmail') && $this->email != WCF::getUser()->email && $this->email != WCF::getUser()->newEmail) {
			if (REGISTER_ACTIVATION_METHOD == 0 || REGISTER_ACTIVATION_METHOD == 2 || mb_strtolower($this->email) == mb_strtolower(WCF::getUser()->email)) {
				// update email
				$this->updateParameters['email'] = $this->email;
				$this->success[] = 'wcf.user.changeEmail.success';
			}
			else if (REGISTER_ACTIVATION_METHOD == 1) {
				// get reactivation code
				$activationCode = UserRegistrationUtil::getActivationCode();
				
				// save as new email
				$this->updateParameters['reactivationCode'] = $activationCode;
				$this->updateParameters['newEmail'] = $this->email;
				
				$messageData = array(
					'username' => WCF::getUser()->username,
					'userID' => WCF::getUser()->userID,
					'activationCode' => $activationCode
				);
				
				$mail = new Mail(array(WCF::getUser()->username => $this->email), WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation.mail.subject'), WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation.mail', $messageData));
				$mail->send();
				$this->success[] = 'wcf.user.changeEmail.needReactivation';
			}
		}
		
		// password
		if (!WCF::getUser()->authData) {
			if (!empty($this->newPassword) || !empty($this->confirmNewPassword)) {
				$this->updateParameters['password'] = $this->newPassword;
				$this->success[] = 'wcf.user.changePassword.success';
			}
		}
		
		// 3rdParty
		if (GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== '') {
			if ($this->githubConnect && WCF::getSession()->getVar('__githubToken')) {
				$this->updateParameters['authData'] = 'github:'.WCF::getSession()->getVar('__githubToken');
				$this->success[] = 'wcf.user.3rdparty.github.connect.success';
				
				WCF::getSession()->unregister('__githubToken');
				WCF::getSession()->unregister('__githubUsername');
			}
			else if ($this->githubDisconnect && StringUtil::startsWith(WCF::getUser()->authData, 'github:')) {
				$this->updateParameters['authData'] = '';
				$this->success[] = 'wcf.user.3rdparty.github.disconnect.success';
			}
		}
		if (TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== '') {
			if ($this->twitterConnect && WCF::getSession()->getVar('__twitterData')) {
				$twitterData = WCF::getSession()->getVar('__twitterData');
				$this->updateParameters['authData'] = 'twitter:'.$twitterData['user_id'];
				$this->success[] = 'wcf.user.3rdparty.twitter.connect.success';
				
				WCF::getSession()->unregister('__twitterData');
				WCF::getSession()->unregister('__twitterUsername');
			}
			else if ($this->twitterDisconnect && StringUtil::startsWith(WCF::getUser()->authData, 'twitter:')) {
				$this->updateParameters['authData'] = '';
				$this->success[] = 'wcf.user.3rdparty.twitter.disconnect.success';
			}
		}
		if (FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== '') {
			if ($this->facebookConnect && WCF::getSession()->getVar('__facebookData')) {
				$facebookData = WCF::getSession()->getVar('__facebookData');
				$this->updateParameters['authData'] = 'facebook:'.$facebookData['id'];
				$this->success[] = 'wcf.user.3rdparty.facebook.connect.success';
				
				WCF::getSession()->unregister('__facebookData');
				WCF::getSession()->unregister('__facebookUsername');
			}
			else if ($this->facebookDisconnect && StringUtil::startsWith(WCF::getUser()->authData, 'facebook:')) {
				$this->updateParameters['authData'] = '';
				$this->success[] = 'wcf.user.3rdparty.facebook.disconnect.success';
			}
		}
		if (GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== '') {
			if ($this->googleConnect && WCF::getSession()->getVar('__googleData')) {
				$googleData = WCF::getSession()->getVar('__googleData');
				$this->updateParameters['authData'] = 'google:'.$googleData['id'];
				$this->success[] = 'wcf.user.3rdparty.google.connect.success';
				
				WCF::getSession()->unregister('__googleData');
				WCF::getSession()->unregister('__googleUsername');
			}
			else if ($this->googleDisconnect && StringUtil::startsWith(WCF::getUser()->authData, 'google:')) {
				$this->updateParameters['authData'] = '';
				$this->success[] = 'wcf.user.3rdparty.google.disconnect.success';
			}
		}
		
		$data = array();
		if (!empty($this->updateParameters)) {
			$data['data'] = array_merge($this->additionalFields, $this->updateParameters);
		}
		if (!empty($this->updateOptions)) {
			$data['options'] = $this->updateOptions;
		}
		
		$this->objectAction = new UserAction(array(WCF::getUser()), 'update', $data);
		$this->objectAction->executeAction();
		
		// update cookie
		if (isset($_COOKIE[COOKIE_PREFIX.'password']) && isset($this->updateParameters['password'])) {
			// reload user
			$user = new User(WCF::getUser()->userID);
			
			HeaderUtil::setCookie('password', PasswordUtil::getSaltedHash($this->updateParameters['password'], $user->password), TIME_NOW + 365 * 24 * 3600);
		}
		
		$this->saved();
		
		$this->success = array_merge($this->success, WCF::getTPL()->get('success') ?: array());
		
		// show success message
		WCF::getTPL()->assign('success', $this->success);
		
		// reset password
		$this->password = '';
		$this->newPassword = $this->confirmNewPassword = '';
	}
}
