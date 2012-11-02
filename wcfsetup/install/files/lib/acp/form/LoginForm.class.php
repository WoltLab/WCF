<?php
namespace wcf\acp\form;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\authentication\EmailUserAuthentication;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the acp login form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LoginForm extends AbstractForm {
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
	 * @var	wcf\data\user\User
	 */
	public $user;
	
	/**
	 * given forward url
	 * @var	string
	 */
	public $url = null;
	
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
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['url'])) $this->url = $_REQUEST['url'];
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
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
			$this->user = UserAuthenticationFactory::getUserAuthentication()->loginManually($this->username, $this->password);
		}
		catch (UserInputException $e) {
			// TODO: create an option for the authentication with email address
			if (true) {
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
			else {
				throw $e;
			}
		}
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
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
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// change user
		WCF::getSession()->changeUser($this->user);
		$this->saved();
		
		if (!empty($this->url)) {
			// append session
			if (StringUtil::indexOf($this->url, '?') !== false) $this->url .= SID_ARG_2ND_NOT_ENCODED;
			else $this->url .= SID_ARG_1ST;
			HeaderUtil::redirect($this->url);
		}
		else {
			$application = ApplicationHandler::getInstance()->getActiveApplication();
			$path = $application->getPageURL() . 'acp/index.php' . SID_ARG_1ST;
			HeaderUtil::redirect($path);
		}
		exit;
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
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
	 * @see	wcf\page\IPage::assignVariables()
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
