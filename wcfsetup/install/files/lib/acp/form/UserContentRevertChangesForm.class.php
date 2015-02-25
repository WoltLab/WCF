<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\edit\EditHistoryManager;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Shows the user content revert changes form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserContentRevertChangesForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_EDIT_HISTORY');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.canBulkRevertContentChanges');
	
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	
	/**
	 * ids of the relevant users
	 * @var	array<integer>
	 */
	public $userIDs = array();
	
	/**
	 * relevant users
	 * @var	array<\wcf\data\user\User>
	 */
	public $users = array();
	
	/**
	 * timeframe to consider
	 * @var	integer
	 */
	public $timeframe = 7;
	
	/**
	 * id of the user clipboard item object type
	 * @var	integer
	 */
	protected $objectTypeID = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get object type id
		$this->objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
		// get user
		$this->users = ClipboardHandler::getInstance()->getMarkedItems($this->objectTypeID);
		if (empty($this->users)) {
			throw new IllegalLinkException();
		}
		$this->userIDs = array_keys($this->users);
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['timeframe'])) $this->timeframe = intval($_POST['timeframe']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->timeframe < 1) {
			throw new UserInputException('timeframe');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		EditHistoryManager::getInstance()->bulkRevert($this->userIDs, $this->timeframe * 86400);
		
		// reset clipboard
		ClipboardHandler::getInstance()->removeItems($this->objectTypeID);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('message', 'wcf.global.success');
		WCF::getTPL()->display('success');
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'timeframe' => $this->timeframe
		));
	}
}
