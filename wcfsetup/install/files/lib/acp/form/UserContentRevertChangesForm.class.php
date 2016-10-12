<?php
namespace wcf\acp\form;
use wcf\data\user\User;
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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserContentRevertChangesForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_EDIT_HISTORY'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.canBulkRevertContentChanges'];
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	
	/**
	 * ids of the relevant users
	 * @var	integer[]
	 */
	public $userIDs = [];
	
	/**
	 * relevant users
	 * @var	User[]
	 */
	public $users = [];
	
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['timeframe'])) $this->timeframe = intval($_POST['timeframe']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if ($this->timeframe < 1) {
			throw new UserInputException('timeframe');
		}
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'timeframe' => $this->timeframe
		]);
	}
}
