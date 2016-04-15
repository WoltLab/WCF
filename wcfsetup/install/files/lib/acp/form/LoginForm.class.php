<?php
namespace wcf\acp\form;
use wcf\data\user\authentication\failure\UserAuthenticationFailure;
use wcf\data\user\authentication\failure\UserAuthenticationFailureAction;
use wcf\data\user\User;
use wcf\form\AbstractCaptchaForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\system\user\authentication\EmailUserAuthentication;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the acp login form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LoginForm extends AbstractCaptchaForm {
	/**
	 * given login username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * given login password
	 * @var	string
	 */
	public $password = '';
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * given forward url
	 * @var	string
	 */
	public $url = null;
	
	/**
	 * @see	\wcf\form\AbstractCaptchaForm::$useCaptcha
	 */
	public $useCaptcha = false;
	
	/**
	 * Creates a new LoginForm object.
	 */
	public function __run() {
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		parent::__run();
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['url'])) {
			$this->url = StringUtil::trim($_REQUEST['url']);
			
			// discard URL if it is not an absolute URL of local content
			if (!ApplicationHandler::getInstance()->isInternalURL($this->url)) {
				$this->url = '';
			}
		}
		
		// check authentication failures
		if (ENABLE_USER_AUTHENTICATION_FAILURE) {
			$failures = UserAuthenticationFailure::countIPFailures(UserUtil::getIpAddress());
			if (USER_AUTHENTICATION_FAILURE_IP_BLOCK && $failures >= USER_AUTHENTICATION_FAILURE_IP_BLOCK) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.login.blocked'));
			}
			if (USER_AUTHENTICATION_FAILURE_IP_CAPTCHA && $failures >= USER_AUTHENTICATION_FAILURE_IP_CAPTCHA) {
				$this->useCaptcha = true;
			}
			else if (USER_AUTHENTICATION_FAILURE_USER_CAPTCHA) {
				if (isset($_POST['username'])) {
					$user = User::getUserByUsername(StringUtil::trim($_POST['username']));
					if (!$user->userID) $user = User::getUserByEmail(StringUtil::trim($_POST['username']));
					
					if ($user->userID) {
						$failures = UserAuthenticationFailure::countUserFailures($user->userID);
						if (USER_AUTHENTICATION_FAILURE_USER_CAPTCHA && $failures >= USER_AUTHENTICATION_FAILURE_USER_CAPTCHA) {
							$this->useCaptcha = true;
						}
					}
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
	}
	
	/**
	 * Validates the user access data.
	 */
	protected function validateUser() {
		try {
			$this->user = UserAuthenticationFactory::getInstance()->getUserAuthentication()->loginManually($this->username, $this->password);
		}
		catch (UserInputException $e) {
			if ($e->getField() == 'username') {
				try {
					$this->user = EmailUserAuthentication::getInstance()->loginManually($this->username, $this->password);
				}
				catch (UserInputException $e2) {
					if ($e2->getField() == 'username') throw $e;
					throw $e2;
				}
			}
			else {
				throw $e;
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::submit()
	 */
	public function submit() {
		parent::submit();
		
		// save authentication failure
		if (ENABLE_USER_AUTHENTICATION_FAILURE) {
			if ($this->errorField == 'username' || $this->errorField == 'password') {
				$action = new UserAuthenticationFailureAction(array(), 'create', array(
					'data' => array(
						'environment' => (RequestHandler::getInstance()->isACPRequest() ? 'admin' : 'user'),
						'userID' => ($this->user !== null ? $this->user->userID : null),
						'username' => $this->username,
						'time' => TIME_NOW,
						'ipAddress' => UserUtil::getIpAddress(),
						'userAgent' => UserUtil::getUserAgent()
					)
				));
				$action->executeAction();
				
				if ($this->captchaObjectType) {
					$this->captchaObjectType->getProcessor()->reset();
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!WCF::getSession()->hasValidCookie()) {
			throw new UserInputException('cookie');
		}
		
		// error handling
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		
		if (empty($this->password)) {
			throw new UserInputException('password');
		}
		
		$this->validateUser();
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// change user
		WCF::getSession()->changeUser($this->user);
		$this->saved();
		
		if (!empty($this->url)) {
			HeaderUtil::redirect($this->url);
		}
		else {
			if (RequestHandler::getInstance()->inRescueMode()) {
				$path = RouteHandler::getHost() . RouteHandler::getPath();
			}
			else {
				$application = ApplicationHandler::getInstance()->getActiveApplication();
				$path = $application->getPageURL() . 'acp/';
			}
			
			HeaderUtil::redirect($path);
		}
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get preferred username
		if (empty($_POST)) {
			if (isset($_COOKIE[COOKIE_PREFIX.'userID'])) {
				$user = new User(intval($_COOKIE[COOKIE_PREFIX.'userID']));
				if ($user->userID) $this->username = $user->username;
			}
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
			'url' => $this->url
		));
	}
}
