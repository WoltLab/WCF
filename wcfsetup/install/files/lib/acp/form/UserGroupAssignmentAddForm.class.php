<?php
namespace wcf\acp\form;
use wcf\data\user\group\assignment\UserGroupAssignmentAction;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new automatic user group assignment.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserGroupAssignmentAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.assignment';
	
	/**
	 * list of grouped user group assignment condition object types
	 * @var	array
	 */
	public $conditions = array();
	
	/**
	 * id of the selected user group
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * true if the automatic assignment is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canManageGroupAssignment');
	
	/**
	 * title of the user group assignment
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * list of selectable user groups
	 * @var	array<\wcf\data\user\group\UserGroup>
	 */
	public $userGroups = array();
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'groupedObjectTypes' => $this->conditions,
			'groupID' => $this->groupID,
			'isDisabled' => $this->isDisabled,
			'title' => $this->title,
			'userGroups' => $this->userGroups
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$this->userGroups = UserGroup::getGroupsByType(array(), array(
			UserGroup::EVERYONE,
			UserGroup::GUESTS,
			UserGroup::USERS
		));
		foreach ($this->userGroups as $key => $userGroup) {
			if (!$userGroup->isAccessible()) {
				unset($this->userGroups[$key]);
			}
		}
		
		uasort($this->userGroups, function(UserGroup $groupA, UserGroup $groupB) {
			return strcmp($groupA->getName(), $groupB->getName());
		});
		
		$this->conditions = UserGroupAssignmentHandler::getInstance()->getGroupedObjectTypes('com.woltlab.wcf.condition.userGroupAssignment');
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->readFormParameters();
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new UserGroupAssignmentAction(array(), 'create', array(
			'data' => array_merge($this->additionalFields, array(
				'groupID' => $this->groupID,
				'isDisabled' => $this->isDisabled,
				'title' => $this->title
			))
		));
		$returnValues = $this->objectAction->executeAction();
		
		// transform conditions array into one-dimensional array
		$conditions = array();
		foreach ($this->conditions as $groupedObjectTypes) {
			$conditions = array_merge($conditions, $groupedObjectTypes);
		}
		
		ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->assignmentID, $conditions);
		
		$this->saved();
		
		// reset values
		$this->groupID = 0;
		$this->isDisabled = 0;
		$this->title = '';
		
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->reset();
			}
		}
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		if (strlen($this->title) > 255) {
			throw new UserInputException('title', 'tooLong');
		}
		
		if (!isset($this->userGroups[$this->groupID])) {
			throw new UserInputException('groupID', 'notValid');
		}
		
		$hasData = false;
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->validate();
				
				if (!$hasData && $condition->getProcessor()->getData() !== null) {
					$hasData = true;
				}
			}
		}
		
		if (!$hasData) {
			throw new UserInputException('conditions');
		}
	}
}
