<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	form
 * @category	Community Framework
 */
class NewPasswordForm extends AbstractForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @see	wcf\page\AbstractPage::$enableTracking
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
	 * @var	wcf\data\user\User
	 */
	public $user;
	
	/**
	 * new password
	 * @var	string
	 */
	public $newPassword = '';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['u'])) $this->userID = intval($_REQUEST['u']);
		if (isset($_REQUEST['k'])) $this->lostPasswordKey = StringUtil::trim($_REQUEST['k']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// get user
		$this->user = new User($this->userID);
		
		if (!$this->user->userID) {
			throw new UserInputException('userID', 'invalid');
		}
		if (!$this->user->lostPasswordKey) {
			throw new UserInputException('lostPasswordKey');
		}
		
		if ($this->user->lostPasswordKey != $this->lostPasswordKey) {
			throw new UserInputException('lostPasswordKey', 'invalid');
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// generate new password
		$this->newPassword = PasswordUtil::getRandomPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
		
		// update user
		$userEditor = new UserEditor($this->user);
		$userEditor->update(array(
			'password' => $this->newPassword,
			'lastLostPasswordRequestTime' => 0,
			'lostPasswordKey' => ''
		));
		
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
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'userID' => $this->userID,
			'lostPasswordKey' => $this->lostPasswordKey
		));
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		AbstractPage::readData();
		
		if (!empty($_POST) || (!empty($this->userID) && !empty($this->lostPasswordKey))) {
			$this->submit();
		}
	}
}
