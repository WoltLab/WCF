<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserEditor;
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
	public $templateName = 'userAssignToGroup';
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = array('admin.user.canEditUser');
	
	public $userIDs = array();
	public $groupIDs = array();
	public $users = array();
	public $groups = array();
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs']));
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
			
			$userEditor = new UserEditor($user);
			$userEditor->addToGroups($groups[$user->userID], false, false);
		}
		
		// TODO: Implement unmarkAll()
		//UserEditor::unmarkAll();
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
		
		if (!count($_POST)) {
			// get marked user ids
			$markedUsers = WCF::getSession()->getVar('markedUsers');
			if (is_array($markedUsers)) {
				$this->userIDs = $markedUsers;
			}
			if (empty($this->userIDs)) throw new IllegalLinkException();
		}
		
		$this->users = User::getUsers($this->userIDs);
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
