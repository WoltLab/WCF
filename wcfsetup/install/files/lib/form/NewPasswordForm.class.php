<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Shows the new password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class NewPasswordForm extends AbstractForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
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
	 * @var	User
	 */
	public $user;
	
	/**
	 * new password
	 * @var	string
	 */
	public $newPassword = '';
	
	/**
	 * @var	mixed[]
	 */
	public $newPasswordStrengtVerdict = [];
	
	/**
	 * confirmed new password
	 * @var	string
	 */
	public $confirmNewPassword = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['id']) && isset($_GET['k'])) {
			$this->userID = intval($_GET['id']);
			$this->lostPasswordKey = StringUtil::trim($_GET['k']);
			if (!$this->userID || !$this->lostPasswordKey) throw new IllegalLinkException();
			
			$this->user = new User($this->userID);
			if (!$this->user->userID) throw new IllegalLinkException();
			
			if (!$this->user->lostPasswordKey) {
				$this->throwInvalidLinkException(); 
			}
			if (!\hash_equals($this->user->lostPasswordKey, $this->lostPasswordKey)) {
				$this->throwInvalidLinkException();
			}
			// expire lost password requests after a day
			if ($this->user->lastLostPasswordRequestTime < TIME_NOW - 86400) {
				$this->throwInvalidLinkException();
			}
			
			WCF::getSession()->register('lostPasswordRequest', [
				'userID' => $this->user->userID,
				'key' => $this->user->lostPasswordKey
			]);
		}
		else {
			if (!is_array(WCF::getSession()->getVar('lostPasswordRequest'))) throw new PermissionDeniedException();
			$this->userID = intval(WCF::getSession()->getVar('lostPasswordRequest')['userID']);
			
			$this->user = new User($this->userID);
			if (!$this->user->userID) throw new IllegalLinkException();
			if (!\hash_equals($this->user->lostPasswordKey, WCF::getSession()->getVar('lostPasswordRequest')['key'])) {
				$this->throwInvalidLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['newPassword'])) $this->newPassword = $_POST['newPassword'];
		if (isset($_POST['newPassword_passwordStrengthVerdict'])) {
			try {
				$this->newPasswordStrengthVerdict = JSON::decode($_POST['newPassword_passwordStrengthVerdict']);
			}
			catch (SystemException $e) {
				// ignore
			}
		}
		if (isset($_POST['confirmNewPassword'])) $this->confirmNewPassword = $_POST['confirmNewPassword'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->newPassword)) {
			throw new UserInputException('newPassword');
		}
		
		if (empty($this->confirmNewPassword)) {
			throw new UserInputException('confirmNewPassword');
		}
		
		if (($this->newPasswordStrengthVerdict['score'] ?? 4) < PASSWORD_MIN_SCORE) {
			throw new UserInputException('newPassword', 'notSecure');
		}
		
		if ($this->newPassword != $this->confirmNewPassword) {
			throw new UserInputException('confirmNewPassword', 'notEqual');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		WCF::getSession()->unregister('lostPasswordRequest');
		
		// update user
		$this->objectAction = new UserAction([$this->user], 'update', [
			'data' => array_merge($this->additionalFields, [
				'password' => $this->newPassword,
				'lastLostPasswordRequestTime' => 0,
				'lostPasswordKey' => ''
			])
		]);
		$this->objectAction->executeAction();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.user.newPassword.success', ['user' => $this->user]));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'user' => $this->user,
			'newPassword' => $this->newPassword,
			'confirmNewPassword' => $this->confirmNewPassword,
			'passwordRulesAttributeValue' => UserRegistrationUtil::getPasswordRulesAttributeValue()
		]);
	}
	
	private function throwInvalidLinkException() {
		throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.newPassword.error.invalidLink'));
	}
}
