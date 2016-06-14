<?php
namespace wcf\acp\form;
use wcf\data\application\Application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationEditor;
use wcf\data\application\ApplicationList;
use wcf\data\user\authentication\failure\UserAuthenticationFailure;
use wcf\data\user\authentication\failure\UserAuthenticationFailureAction;
use wcf\data\user\User;
use wcf\form\AbstractCaptchaForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\style\StyleHandler;
use wcf\system\user\authentication\EmailUserAuthentication;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the rescue mode form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class RescueModeForm extends AbstractCaptchaForm {
	/**
	 * @var	Application[]
	 */
	public $applications;
	
	/**
	 * @var	string[]
	 */
	public $applicationValues = [];
	
	/**
	 * login password
	 * @var	string
	 */
	public $password = '';
	
	/**
	 * @var	User
	 */
	public $user;
	
	/**
	 * login username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * @inheritDoc
	 */
	public $useCaptcha = false;
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		if (!WCFACP::inRescueMode()) {
			// redirect to currently active application's ACP
			HeaderUtil::redirect(ApplicationHandler::getInstance()->getActiveApplication()->getPageURL() . 'acp/');
			exit;
		}
		
		parent::__run();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// request style generation to prevent issues when using the proxy parameter
		StyleHandler::getInstance()->getStylesheet(true);
		
		if (isset($_GET['proxy'])) {
			switch ($_GET['proxy']) {
				case 'css':
					$file = WCF_DIR . 'acp/style/style.css';
					
					header('Content-Type: text/css');
					break;
				
				case 'logo':
					$file = WCF_DIR . 'images/default-logo.png';
					
					header('Content-Type: image/png');
					break;
				
				default:
					throw new IllegalLinkException();
					break;
			}
			
			header('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600).' GMT');
			header('Last-Modified: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Cache-Control: public, max-age=3600');
			
			readfile($file);
			exit;
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
		
		// read applications
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		$this->applications = $applicationList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['applicationValues']) && is_array($_POST['applicationValues'])) $this->applicationValues = $_POST['applicationValues'];
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
		
		// simulate login in order to access permissions
		WCF::getSession()->disableUpdate();
		WCF::getSession()->changeUser($this->user, true);
		
		if (!WCF::getSession()->getPermission('admin.configuration.canManageApplication')) {
			throw new UserInputException('username', 'notAuthorized');
		}
	}
	
	protected function validateApplications() {
		$usedPaths = [];
		foreach ($this->applications as $application) {
			$packageID = $application->packageID;
			
			$domainName = $this->applicationValues[$packageID]['domainName'];
			$domainName = preg_replace('~^https?://~', '', $domainName);
			$domainName = FileUtil::removeTrailingSlash($domainName);
			$domainName = StringUtil::trim($domainName);
			
			if (empty($domainName)) {
				throw new UserInputException("application_{$packageID}_domainName");
			}
			else if (preg_match('~[/#\?&]~', $domainName)) {
				throw new UserInputException("application_{$packageID}_domainName", 'containsPath');
			}
			
			$domainPath = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash($this->applicationValues[$packageID]['domainPath']));
			
			$this->applicationValues[$packageID]['domainName'] = $domainName;
			$this->applicationValues[$packageID]['domainPath'] = $domainPath;
			
			if (isset($usedPaths[$domainName])) {
				if (isset($usedPaths[$domainName][$domainPath])) {
					WCF::getTPL()->assign('conflictApplication', $this->applications[$usedPaths[$domainName][$domainPath]]->getPackage());
					throw new UserInputException("application_{$packageID}_domainPath", 'conflict');
				}
			}
			else {
				$usedPaths[$domainName] = [];
			}
			
			$usedPaths[$domainName][$domainPath] = $packageID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function submit() {
		parent::submit();
		
		// save authentication failure
		if (ENABLE_USER_AUTHENTICATION_FAILURE) {
			if ($this->errorField == 'username' || $this->errorField == 'password') {
				$action = new UserAuthenticationFailureAction([], 'create', [
					'data' => [
						'environment' => 'admin',
						'userID' => ($this->user !== null ? $this->user->userID : null),
						'username' => $this->username,
						'time' => TIME_NOW,
						'ipAddress' => UserUtil::getIpAddress(),
						'userAgent' => UserUtil::getUserAgent()
					]
				]);
				$action->executeAction();
				
				if ($this->captchaObjectType) {
					$this->captchaObjectType->getProcessor()->reset();
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// bypass security token validation
		$_POST['t'] = WCF::getSession()->getSecurityToken();
		
		parent::validate();
		
		// error handling
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		
		if (empty($this->password)) {
			throw new UserInputException('password');
		}
		
		$this->validateUser();
		$this->validateApplications();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// update applications
		foreach ($this->applications as $application) {
			$applicationEditor = new ApplicationEditor($application);
			$applicationEditor->update([
				'domainName' => $this->applicationValues[$application->packageID]['domainName'],
				'domainPath' => $this->applicationValues[$application->packageID]['domainPath'],
				'cookieDomain' => $this->applicationValues[$application->packageID]['domainName']
			]);
		}
		
		// rebuild cookie domain and paths
		$applicationAction = new ApplicationAction([], 'rebuild');
		$applicationAction->executeAction();
		
		// reload currently active application to avoid outdated cache data
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		$application = new Application($application->packageID);
		
		// redirect to ACP of currently active application
		HeaderUtil::redirect($application->getPageURL() . 'acp/');
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get preferred username
		if (empty($_POST)) {
			if (isset($_COOKIE[COOKIE_PREFIX.'userID'])) {
				$user = new User(intval($_COOKIE[COOKIE_PREFIX.'userID']));
				if ($user->userID) $this->username = $user->username;
			}
			
			foreach ($this->applications as $application) {
				$this->applicationValues[$application->packageID] = [
					'domainName' => $application->domainName,
					'domainPath' => $application->domainPath
				];
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'applications' => $this->applications,
			'applicationValues' => $this->applicationValues,
			'pageURL' => WCFACP::getRescueModePageURL() . 'acp/index.php?rescue-mode/',
			'password' => $this->password,
			'username' => $this->username
		]);
	}
}
