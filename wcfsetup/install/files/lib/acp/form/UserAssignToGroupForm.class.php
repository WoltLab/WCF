<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the assign user to group form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserAssignToGroupForm extends ACPForm {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'userAssignToGroup';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditUser');
	
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	
	public $userIDs = array();
	public $groupIDs = array();
	public $users = array();
	public $groups = array();
	
	/**
	 * clipboard item type id
	 * @var	integer
	 */
	protected $typeID = null;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get type id
		$this->typeID = ClipboardHandler::getInstance()->getTypeID('com.woltlab.wcf.user');
		if ($this->typeID === null) {
			throw new SystemException("clipboard item type 'com.woltlab.wcf.user' is unknown.");
		}
		
		// get user ids
		$users = ClipboardHandler::getInstance()->getMarkedItems($this->typeID);
		if (!isset($users['com.woltlab.wcf.user']) || empty($users['com.woltlab.wcf.user'])) throw new IllegalLinkException();
		
		// load users
		$this->userIDs = array_keys($users['com.woltlab.wcf.user']);
		$this->users = $users['com.woltlab.wcf.user'];
	}
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
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
	 * @see wcf\form\IForm::save()
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
			
			$userEditor = new UserEditor($user);
			$userEditor->addToGroups($groupsIDs, true, false);
		}
		
		ClipboardHandler::getInstance()->removeItems($this->typeID);
		SessionHandler::resetSessions($this->userIDs);
		
		$this->saved();
		
		WCF::getTPL()->assign('message', 'wcf.acp.user.assignToGroup.success');
		WCF::getTPL()->display('success');
		exit;
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readGroups();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
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
