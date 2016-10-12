<?php
namespace wcf\data\user\group\assignment;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\condition\ConditionHandler;

/**
 * Executes user group assignment-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Assignment
 * 
 * @method	UserGroupAssignment		create()
 * @method	UserGroupAssignmentEditor[]	getObjects()
 * @method	UserGroupAssignmentEditor	getSingleObject()
 */
class UserGroupAssignmentAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.user.canManageGroupAssignment'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.user.canManageGroupAssignment'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'toggle', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.userGroupAssignment', $this->objectIDs);
		
		return parent::delete();
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $assignment) {
			$assignment->update([
				'isDisabled' => $assignment->isDisabled ? 0 : 1
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
}
