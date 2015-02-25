<?php
namespace wcf\data\user\group\assignment;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;

/**
 * Represents an automatic assignement to a user group.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.assignment
 * @category	Community Framework
 */
class UserGroupAssignment extends DatabaseObject implements IRouteController {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'assignmentID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_group_assignment';
	
	/**
	 * Returns the conditions of the automatic assignement to a user group.
	 * 
	 * @return	array<\wcf\data\condition\Condition>
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.userGroupAssignment', $this->assignmentID);
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
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
