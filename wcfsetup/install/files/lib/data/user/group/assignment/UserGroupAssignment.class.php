<?php
namespace wcf\data\user\group\assignment;
use wcf\data\condition\Condition;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;

/**
 * Represents an automatic assignment to a user group.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Assignment
 *
 * @property-read	integer		$assignmentID		unique id of the automatic user group assignment
 * @property-read	integer		$groupID		id of the user group to which users are automatically assigned
 * @property-read	string		$title			title of the automatic user group assignment
 * @property-read	integer		$isDisabled		is `1` if the user group assignment is disabled and thus not checked for automatic assignments, otherwise `0`
 */
class UserGroupAssignment extends DatabaseObject implements IRouteController {
	/**
	 * Returns the conditions of the automatic assignment to a user group.
	 * 
	 * @return	Condition[]
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.userGroupAssignment', $this->assignmentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the user group the automatic assignment belongs to.
	 * 
	 * @return	UserGroup
	 */
	public function getUserGroup() {
		return UserGroup::getGroupByID($this->groupID);
	}
}
