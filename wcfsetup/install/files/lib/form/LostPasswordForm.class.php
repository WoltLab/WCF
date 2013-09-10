<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the lost password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class LostPasswordForm extends RecaptchaForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @see	wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * email address
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * user object
	 * @var	wcf\data\user\User
	 */
	public $user;
	
	/**
	 * @see	wcf\form\RecaptchaForm::$useCaptcha
	 */
	public $useCaptcha = LOST_PASSWORD_USE_CAPTCHA;
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->username) && empty($this->email)) {
			throw new UserInputException('username');
		}
		
		if (!empty($this->username)) {
			$this->user = User::getUserByUsername($this->username);
			if (!$this->user->userID) {
				throw new UserInputException('username', 'notFound');
			}
		}
		else {
			$this->user = User::getUserByEmail($this->email);
			if (!$this->user->userID) {
				throw new UserInputException('email', 'notFound');
			}
		}
		
		// check if using 3rd party @author dtdesign
		if ($this->user->authData) {
			throw new UserInputException('username', '3rdParty');
		}
		
		// check whether a lost password request was sent in the last 24 hours
		if ($this->user->lastLostPasswordRequestTime && TIME_NOW - 86400 < $this->user->lastLostPasswordRequestTime) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.error.tooManyRequests', array('hours' => ceil(($this->user->lastLostPasswordRequestTime - (TIME_NOW - 86400)) / 3600))));
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// generate a new lost password key
		$lostPasswordKey = StringUtil::getRandomID();
		
		// save key and request time in database
		$userEditor = new UserEditor($this->user);
		$userEditor->update(array(
			'lostPasswordKey' => $lostPasswordKey,
			'lastLostPasswordRequestTime' => TIME_NOW
		));
		
		// send mail
		$mail = new Mail(array($this->user->username => $this->user->email), WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.mail.subject'), WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.mail', array(
			'username' => $this->user->username,
			'userID' => $this->user->userID,
			'key' => $lostPasswordKey
		)));
		$mail->send();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.lostPassword.mail.sent'));
		exit;
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email
		));
	}
}
