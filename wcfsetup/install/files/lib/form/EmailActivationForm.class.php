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
use wcf\util\UserUtil;

/**
 * Shows the email activation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class EmailActivationForm extends AbstractForm {
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = null;
	
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
		
		if (isset($_GET['u']) && !empty($_GET['u'])) $this->userID = intval($_GET['u']);
		if (isset($_GET['a']) && !empty($_GET['a'])) $this->activationCode = intval($_GET['a']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['u']) && !empty($_POST['u'])) $this->userID = intval($_POST['u']);
		if (isset($_POST['a']) && !empty($_POST['a'])) $this->activationCode = intval($_POST['a']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		EventHandler::getInstance()->fireAction($this, 'validate');
		
		// check given user id
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new UserInputException('u', 'notValid');
		}
		
		// user is already enabled
		if ($this->user->reactivationCode == 0) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.emailActivation.error.emailAlreadyEnabled'));
		}
		
		// check whether the new email isn't unique anymore
		if (!UserUtil::isAvailableEmail($this->user->newEmail)) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.email.error.notUnique'));
		}
		
		// check given activation code
		if ($this->user->reactivationCode != $this->activationCode) {
			throw new UserInputException('a', 'notValid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// enable new email
		$this->objectAction = new UserAction([$this->user], 'update', [
			'data' => array_merge($this->additionalFields, [
				'email' => $this->user->newEmail,
				'newEmail' => '',
				'reactivationCode' => 0
			])
		]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.emailActivation.success'));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'u' => $this->userID,
			'a' => $this->activationCode
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		if (REGISTER_ACTIVATION_METHOD != 1) {
			throw new IllegalLinkException();
		}
		
		if (empty($_POST) && $this->userID !== null && $this->activationCode != 0) {
			$this->submit();
		}
		
		parent::show();
	}
}
