<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user activation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class RegisterActivationForm extends AbstractForm {
	/**
	 * username
	 * @var	string
	 */
	public $username = null;
	
	/**
	 * activation code
	 * @var	string
	 */
	public $activationCode = '';
	
	/**
	 * User object
	 * @var	User
	 */
	public $user = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_GET['u'])) {
			$userID = intval($_GET['u']);
			$this->user = new User($userID);
			if ($this->user->userID) $this->username = $this->user->username;
		}
		if (!empty($_GET['a'])) $this->activationCode = StringUtil::trim($_GET['a']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) {
			$this->username = StringUtil::trim($_POST['username']);
			$this->user = User::getUserByUsername($this->username);
		}
		if (isset($_POST['activationCode'])) $this->activationCode = StringUtil::trim($_POST['activationCode']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		EventHandler::getInstance()->fireAction($this, 'validate');
		
		// check given user name
		if ($this->user === null || !$this->user->userID) {
			throw new UserInputException('username', 'notFound');
		}
		
		// user email is already confirmed
		if ($this->user->isEmailConfirmed()) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.registerActivation.error.userAlreadyEnabled'));
		}
		
		// check given activation code
		if (!\hash_equals($this->user->emailConfirmed, $this->activationCode)) {
			throw new UserInputException('activationCode', 'invalid');
		}
		
		if (!empty($this->user->getBlacklistMatches())) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// enable user
		$this->objectAction = new UserAction([$this->user], 'confirmEmail', ['skipNotification' => true]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// forward to index page
		if ($this->user->requiresAdminActivation()) {
			$redirectText = WCF::getLanguage()->getDynamicVariable('wcf.user.registerActivation.success.awaitAdminActivation');
		}
		else {
			$redirectText = WCF::getLanguage()->getDynamicVariable('wcf.user.registerActivation.success');
		}
		
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), $redirectText, 10);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'username' => $this->username,
			'activationCode' => $this->activationCode
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		if (!(REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER)) {
			throw new IllegalLinkException();
		}
		
		if (empty($_POST) && $this->user !== null && $this->activationCode != 0) {
			$this->submit();
		}
		
		if ($this->user === null && !empty(WCF::getUser()->getBlacklistMatches())) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
