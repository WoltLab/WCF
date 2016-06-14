<?php
namespace wcf\system\user\group\assignment;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Handles user group assignment-related matters.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Group\Assignment
 */
class UserGroupAssignmentHandler extends SingletonFactory {
	/**
	 * list of grouped user group assignment condition object types
	 * @var	ObjectType[][]
	 */
	protected $groupedObjectTypes = [];
	
	/**
	 * Checks if the users with the given ids should be assigned to new user
	 * groups.
	 * 
	 * Note: This method uses the user ids as a parameter instead of user objects
	 * on purpose to make sure the latest data of the users are fetched.
	 * 
	 * @param	integer[]		$userIDs
	 */
	public function checkUsers(array $userIDs) {
		if (empty($userIDs)) return;
		
		$userList = new UserList();
		$userList->setObjectIDs($userIDs);
		$userList->readObjects();
		
		/** @var UserGroupAssignment[] $assignments */
		$assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();
		foreach ($userList as $user) {
			$groupIDs = $user->getGroupIDs();
			$newGroupIDs = [];
			
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
				$userAction = new UserAction([$user], 'addToGroups', [
					'addDefaultGroups' => false,
					'deleteOldGroups' => false,
					'groups' => $newGroupIDs
				]);
				$userAction->executeAction();
			}
		}
	}
	
	/**
	 * Returns the list of grouped user group assignment condition object types.
	 * 
	 * @return	ObjectType[][]
	 */
	public function getGroupedObjectTypes() {
		return $this->groupedObjectTypes;
	}
	
	/**
	 * Returns the users who fullfil all conditions of the given user group
	 * assignment.
	 * 
	 * @param	\wcf\data\user\group\assignment\UserGroupAssignment	$assignment
	 * @return	User[]
	 */
	public function getUsers(UserGroupAssignment $assignment) {
		$userList = new UserList();
		$userList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID = ?)', [
			$assignment->groupID
		]);
		
		$conditions = $assignment->getConditions();
		foreach ($conditions as $condition) {
			$condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
		}
		$userList->readObjects();
		
		return $userList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.userGroupAssignment');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($this->groupedObjectTypes[$objectType->conditiongroup])) {
				$this->groupedObjectTypes[$objectType->conditiongroup] = [];
			}
			
			$this->groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
	}
}
