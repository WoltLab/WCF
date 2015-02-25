<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the assign user to group form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserAssignToGroupForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditUser');
	
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
	 * ids of the assigned user groups
	 * @var	array<integer>
	 */
	public $groupIDs = array();
	
	/**
	 * relevant users
	 * @var	array<\wcf\data\user\User>
	 */
	public $users = array();
	
	/**
	 * assigned user groups
	 * @var	array<\wcf\data\user\group\UserGroup>
	 */
	public $groups = array();
	
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
		if ($this->objectTypeID === null) {
			throw new SystemException("clipboard item type 'com.woltlab.wcf.user' is unknown.");
		}
		
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
		
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->userIDs)) throw new IllegalLinkException();
		
		// groups
		foreach ($this->groupIDs as $groupID) {
			$group = new UserGroup($groupID);
			if (!$group->groupID) throw new UserInputException('groupIDs');
			if (!$group->isAccessible()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($this->userIDs));
		
		$sql = "SELECT	userID, groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$groups = array();
		while ($row = $statement->fetchArray()) {
			$groups[$row['userID']][] = $row['groupID'];
		}
		
		foreach ($this->users as $user) {
			if (!UserGroup::isAccessibleGroup($groups[$user->userID])) {
				throw new PermissionDeniedException();
			}
			
			$groupsIDs = array_merge($groups[$user->userID], $this->groupIDs);
			$groupsIDs = array_unique($groupsIDs);
			
			$action = new UserAction(array(new UserEditor($user)), 'addToGroups', array(
				'groups' => $groupsIDs,
				'addDefaultGroups' => false
			));
			$action->executeAction();
		}
		
		ClipboardHandler::getInstance()->removeItems($this->objectTypeID);
		SessionHandler::resetSessions($this->userIDs);
		
		$this->saved();
		
		WCF::getTPL()->assign(array(
			'groupIDs' => $this->groupIDs,
			'message' => 'wcf.acp.user.assignToGroup.success',
			'users' => $this->users
		));
		WCF::getTPL()->display('success');
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readGroups();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'groupIDs' => $this->groupIDs,
			'groups' => $this->groups
		));
	}
	
	/**
	 * Get a list of available groups.
	 */
	protected function readGroups() {
		$this->groups = UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS));
	}
}
