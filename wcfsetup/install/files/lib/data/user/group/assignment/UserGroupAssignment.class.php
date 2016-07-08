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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Assignment
 *
 * @property-read	integer		$assignmentID
 * @property-read	integer		$groupID
 * @property-read	string		$title
 * @property-read	integer		$isDisabled
 */
class UserGroupAssignment extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'assignmentID';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_group_assignment';
	
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
	 * @return	\wcf\data\user\group\UserGroup
	 */
	public function getUserGroup() {
		return UserGroup::getGroupByID($this->groupID);
	}
}
