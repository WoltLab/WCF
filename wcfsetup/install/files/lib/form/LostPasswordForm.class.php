<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the lost password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class LostPasswordForm extends AbstractCaptchaForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
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
	 * @var	User
	 */
	public $user;
	
	/**
	 * @inheritDoc
	 */
	public $useCaptcha = LOST_PASSWORD_USE_CAPTCHA;
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @inheritDoc
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
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.error.tooManyRequests', ['hours' => ceil(($this->user->lastLostPasswordRequestTime - (TIME_NOW - 86400)) / 3600)]));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// generate a new lost password key
		$lostPasswordKey = bin2hex(\random_bytes(20));
		
		// save key and request time in database
		$this->objectAction = new UserAction([$this->user], 'update', [
			'data' => array_merge($this->additionalFields, [
				'lostPasswordKey' => $lostPasswordKey,
				'lastLostPasswordRequestTime' => TIME_NOW
			])
		]);
		$this->objectAction->executeAction();
		
		// reload object
		$this->user = new User($this->user->userID);
		
		$email = new Email();
		$email->addRecipient(new UserMailbox($this->user));
		$email->setSubject($this->user->getLanguage()->getDynamicVariable('wcf.user.lostPassword.mail.subject'));
		$email->setBody(new MimePartFacade([
			new RecipientAwareTextMimePart('text/html', 'email_lostPassword'),
			new RecipientAwareTextMimePart('text/plain', 'email_lostPassword')
		]));
		$email->send();
		
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.user.lostPassword.mail.sent'));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'username' => $this->username,
			'email' => $this->email
		]);
	}
}
