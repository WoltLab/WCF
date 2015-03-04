<?php
namespace wcf\system\user\group\assignment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Handles user group assignment-related matters.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.group.assignment
 * @category	Community Framework
 */
class UserGroupAssignmentHandler extends SingletonFactory {
	/**
	 * list of grouped user group assignment condition object types
	 * @var	array
	 */
	protected $groupedObjectTypes = array();
	
	/**
	 * Checks if the users with the given ids should be assigned to new user
	 * groups.
	 * 
	 * Note: This method uses the user ids as a parameter instead of user objects
	 * on purpose to make sure the latest data of the users are fetched.
	 * 
	 * @param	array<integer>		$userIDs
	 */
	public function checkUsers(array $userIDs) {
		if (empty($userIDs)) return;
		
		$userList = new UserList();
		$userList->getConditionBuilder()->add('user_table.userID IN (?)', array($userIDs));
		$userList->readObjects();
		
		$assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();
		foreach ($userList as $user) {
			$groupIDs = $user->getGroupIDs();
			$newGroupIDs = array();
			
			foreach ($assignments as $assignment) {
				if (in_array($assignment->groupID, $groupIDs) || in_array($assignment->groupID, $newGroupIDs)) {
					continue;
				}
				
				$checkFailed = false;
				$conditions = $assignment->getConditions();
				foreach ($conditions as $condition) {
					if (!$condition->getObjectType()->getProcessor()->checkUser($condition, $user)) {
						$checkFailed = true;
						break;
					}
				}
				
				if (!$checkFailed) {
					$newGroupIDs[] = $assignment->groupID;
				}
			}
			
			if (!empty($newGroupIDs)) {
				$userAction = new UserAction(array($user), 'addToGroups', array(
					'addDefaultGroups' => false,
					'deleteOldGroups' => false,
					'groups' => $newGroupIDs
				));
				$userAction->executeAction();
			}
		}
	}
	
	/**
	 * Returns the list of grouped user group assignment condition object types.
	 * 
	 * @return	array
	 */
	public function getGroupedObjectTypes() {
		return $this->groupedObjectTypes;
	}
	
	/**
	 * Returns the users who fullfil all conditions of the given user group
	 * assignment.
	 * 
	 * @param	\wcf\data\user\group\assignment\UserGroupAssignment	$assignment
	 * @return	array<\wcf\data\user\User>
	 */
	public function getUsers(UserGroupAssignment $assignment) {
		$userList = new UserList();
		$userList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID = ?)', array(
			$assignment->groupID
		));
		
		$conditions = $assignment->getConditions();
		foreach ($conditions as $condition) {
			$condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
		}
		$userList->readObjects();
		
		return $userList->getObjects();
	}
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.userGroupAssignment');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($this->groupedObjectTypes[$objectType->conditiongroup])) {
				$this->groupedObjectTypes[$objectType->conditiongroup] = array();
			}
			
			$this->groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
	}
}
