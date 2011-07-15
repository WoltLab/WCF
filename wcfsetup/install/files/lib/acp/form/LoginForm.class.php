<?php
namespace wcf\acp\form;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\auth\UserAuth;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the acp login form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class LoginForm extends AbstractForm {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'login';
	
	/**
	 * given login username
	 * @var string
	 */
	public $username = '';
	
	/**
	 * given login password
	 * @var string
	 */
	public $password = '';
	
	/**
	 * user object
	 * @var User
	 */
	public $user;
	
	/**
	 * given forward url
	 * @var string
	 */
	public $url = null;
	
	/**
	 * Creates a new LoginForm object.
	 */
	public function __construct() {
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		parent::__construct();
	}
	
	/**
	 * @see wcf\page\Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['url'])) $this->url = $_REQUEST['url'];
	}
	
	/**
	 * @see wcf\form\Form::readFormParameters()
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
		$this->user = UserAuth::getInstance()->loginManually($this->username, $this->password);
	}
	
	/**
	 * @see wcf\form\Form::validate()
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
	 * @see wcf\form\Form::save()
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
			HeaderUtil::redirect('index.php'.SID_ARG_1ST);
		}
		exit;
	}
	
	/**
	 * @see wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();

		// get preferred username
		if (!count($_POST)) {
			if (isset($_COOKIE[COOKIE_PREFIX.'userID'])) {
				$user = new User(intval($_COOKIE[COOKIE_PREFIX.'userID']));
				if ($user->userID) $this->username = $user->username;
			}
		}
	}
	
	/**
	 * @see wcf\page\Page::assignVariables()
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
