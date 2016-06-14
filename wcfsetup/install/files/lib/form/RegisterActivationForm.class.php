<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user activation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @var	integer
	 */
	public $activationCode = '';
	
	/**
	 * User object
	 * @var	\wcf\data\user\User
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
		if (!empty($_GET['a'])) $this->activationCode = intval($_GET['a']);
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
		if (isset($_POST['activationCode'])) $this->activationCode = intval($_POST['activationCode']);
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
		
		// user is already enabled
		if ($this->user->activationCode == 0) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.registerActivation.error.userAlreadyEnabled'));
		}
		
		// check given activation code
		if ($this->user->activationCode != $this->activationCode) {
			throw new UserInputException('activationCode', 'notValid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// enable user
		$this->objectAction = new UserAction([$this->user], 'enable', ['skipNotification' => true]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.registerActivation.success'), 10);
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
		if (REGISTER_ACTIVATION_METHOD != 1) {
			throw new IllegalLinkException();
		}
		
		if (empty($_POST) && $this->user !== null && $this->activationCode != 0) {
			$this->submit();
		}
		
		parent::show();
	}
}
