<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\page\AbstractPage;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Shows the new password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class NewPasswordForm extends AbstractForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * lost password key
	 * @var	string
	 */
	public $lostPasswordKey = '';
	
	/**
	 * User object
	 * @var	\wcf\data\user\User
	 */
	public $user;
	
	/**
	 * new password
	 * @var	string
	 */
	public $newPassword = '';
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['u'])) $this->userID = intval($_REQUEST['u']);
		if (isset($_REQUEST['k'])) $this->lostPasswordKey = StringUtil::trim($_REQUEST['k']);
		
		// disable check for security token for GET requests
		if ($this->userID || $this->lostPasswordKey) {
			$_POST['t'] = WCF::getSession()->getSecurityToken();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// get user
		$this->user = new User($this->userID);
		
		if (!$this->user->userID) {
			throw new UserInputException('userID', 'notValid');
		}
		if (!$this->lostPasswordKey) {
			throw new UserInputException('lostPasswordKey');
		}
		
		if (!$this->user->lostPasswordKey) {
			throw new UserInputException('lostPasswordKey', 'notValid');
		}
		
		if (!PasswordUtil::secureCompare($this->user->lostPasswordKey, $this->lostPasswordKey)) {
			throw new UserInputException('lostPasswordKey', 'notValid');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// generate new password
		$this->newPassword = PasswordUtil::getRandomPassword((REGISTER_PASSWORD_MIN_LENGTH > 12 ? REGISTER_PASSWORD_MIN_LENGTH : 12));
		
		// update user
		$this->objectAction = new UserAction(array($this->user), 'update', array(
			'data' => array_merge($this->additionalFields, array(
				'password' => $this->newPassword,
				'lastLostPasswordRequestTime' => 0,
				'lostPasswordKey' => ''
			))
		));
		$this->objectAction->executeAction();
		
		// send mail
		$mail = new Mail(array($this->user->username => $this->user->email), WCF::getLanguage()->getDynamicVariable('wcf.user.newPassword.mail.subject'), WCF::getLanguage()->getDynamicVariable('wcf.user.newPassword.mail', array(
			'username' => $this->user->username,
			'userID' => $this->user->userID,
			'newPassword' => $this->newPassword
		)));
		$mail->send();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.newPassword.success'));
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'userID' => $this->userID,
			'lostPasswordKey' => $this->lostPasswordKey
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		AbstractPage::readData();
		
		if (!empty($_POST) || (!empty($this->userID) && !empty($this->lostPasswordKey))) {
			$this->submit();
		}
	}
}
