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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'assignment' => $this->assignment
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->groupID = $this->assignment->groupID;
			$this->title = $this->assignment->title;
			
			$conditions = $this->assignment->getConditions();
			foreach ($conditions as $condition) {
				$this->conditions[$condition->getObjectType()->conditiongroup][$condition->objectTypeID]->getProcessor()->setData($condition);
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new UserGroupAssignmentAction(array($this->assignment), 'update', array(
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
		
		ConditionHandler::getInstance()->updateConditions($this->assignment->assignmentID, $this->assignment->getConditions(), $conditions);
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
}
