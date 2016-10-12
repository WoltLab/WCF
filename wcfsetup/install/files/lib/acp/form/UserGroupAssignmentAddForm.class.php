<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectType;
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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserGroupAssignmentAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.assignment';
	
	/**
	 * list of grouped user group assignment condition object types
	 * @var	ObjectType[][]
	 */
	public $conditions = [];
	
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
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canManageGroupAssignment'];
	
	/**
	 * title of the user group assignment
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * list of selectable user groups
	 * @var	UserGroup[]
	 */
	public $userGroups = [];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'groupedObjectTypes' => $this->conditions,
			'groupID' => $this->groupID,
			'isDisabled' => $this->isDisabled,
			'title' => $this->title,
			'userGroups' => $this->userGroups
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->userGroups = UserGroup::getGroupsByType([], [
			UserGroup::EVERYONE,
			UserGroup::GUESTS,
			UserGroup::USERS
		]);
		foreach ($this->userGroups as $key => $userGroup) {
			if (!$userGroup->isAccessible()) {
				unset($this->userGroups[$key]);
			}
		}
		
		uasort($this->userGroups, function(UserGroup $groupA, UserGroup $groupB) {
			return strcmp($groupA->getName(), $groupB->getName());
		});
		
		$this->conditions = UserGroupAssignmentHandler::getInstance()->getGroupedObjectTypes();
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		
		foreach ($this->conditions as $conditions) {
			/** @var ObjectType $condition */
			foreach ($conditions as $condition) {
				$condition->getProcessor()->readFormParameters();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new UserGroupAssignmentAction([], 'create', [
			'data' => array_merge($this->additionalFields, [
				'groupID' => $this->groupID,
				'isDisabled' => $this->isDisabled,
				'title' => $this->title
			])
		]);
		$returnValues = $this->objectAction->executeAction();
		
		// transform conditions array into one-dimensional array
		$conditions = [];
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
	 * @inheritDoc
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
			throw new UserInputException('groupID', 'noValidSelection');
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
