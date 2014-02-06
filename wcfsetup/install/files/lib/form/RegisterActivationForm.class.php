<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
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
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
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
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['activationCode'])) $this->activationCode = intval($_POST['activationCode']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// check given user id
		$this->user = User::getUserByUsername($this->username);
		if (!$this->user->userID) {
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
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// enable user
		$this->objectAction = new UserAction(array($this->user), 'enable');
		$this->objectAction->executeAction();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.registerActivation.success'), 10);
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'activationCode' => $this->activationCode
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		if (REGISTER_ACTIVATION_METHOD != 1) {
			throw new IllegalLinkException();
		}
		
		parent::show();
	}
}
