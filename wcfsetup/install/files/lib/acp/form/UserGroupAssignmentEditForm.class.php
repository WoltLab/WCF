<?php
namespace wcf\acp\form;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentAction;
use wcf\form\AbstractForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the form to edit an existing automatic user group assignment.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserGroupAssignmentEditForm extends UserGroupAssignmentAddForm {
	/**
	 * edited automatic user group assignment
	 * @var	\wcf\data\user\group\assignment\UserGroupAssignment
	 */
	public $assignment = null;
	
	/**
	 * id of the edited automatic user group assignment
	 * @var	integer
	 */
	public $assignmentID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'assignment' => $this->assignment
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->groupID = $this->assignment->groupID;
			$this->title = $this->assignment->title;
			
			$conditions = $this->assignment->getConditions();
			foreach ($conditions as $condition) {
				/** @noinspection PhpUndefinedMethodInspection */
				$this->conditions[$condition->getObjectType()->conditiongroup][$condition->objectTypeID]->getProcessor()->setData($condition);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->assignmentID = intval($_REQUEST['id']);
		$this->assignment = new UserGroupAssignment($this->assignmentID);
		if (!$this->assignment->assignmentID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new UserGroupAssignmentAction([$this->assignment], 'update', [
			'data' => array_merge($this->additionalFields, [
				'groupID' => $this->groupID,
				'isDisabled' => $this->isDisabled,
				'title' => $this->title
			])
		]);
		$this->objectAction->executeAction();
		
		// transform conditions array into one-dimensional array
		$conditions = [];
		foreach ($this->conditions as $groupedObjectTypes) {
			$conditions = array_merge($conditions, $groupedObjectTypes);
		}
		
		ConditionHandler::getInstance()->updateConditions($this->assignment->assignmentID, $this->assignment->getConditions(), $conditions);
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
}
