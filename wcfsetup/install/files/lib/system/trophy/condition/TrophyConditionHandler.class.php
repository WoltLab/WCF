<?php
namespace wcf\system\trophy\condition;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyList;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;

/**
 * Handles trophy conditions. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Trophy\Condition
 * @since	3.1
 */
class TrophyConditionHandler extends SingletonFactory {
	/**
	 * definition name for trophy conditions
	 * @var string
	 */
	const CONDITION_DEFINITION_NAME = 'com.woltlab.wcf.condition.trophy';
	
	/**
	 * list of grouped trophy condition object types
	 * @var	ObjectType[][]
	 */
	protected $groupedObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes(self::CONDITION_DEFINITION_NAME);
		
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($this->groupedObjectTypes[$objectType->conditiongroup])) {
				$this->groupedObjectTypes[$objectType->conditiongroup] = [];
			}
			
			$this->groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
	}
	
	/**
	 * Returns the list of grouped trophy condition object types.
	 *
	 * @return	ObjectType[][]
	 */
	public function getGroupedObjectTypes() {
		return $this->groupedObjectTypes;
	}
	
	/**
	 * Assign trophies based on rules. 
	 * 
	 * @param 	integer		$maxAssigns
	 */
	public function assignTrophies($maxAssigns = 500) {
		$trophyList = new TrophyList();
		$trophyList->getConditionBuilder()->add('awardAutomatically = ?', [1]);
		$trophyList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$trophyList->readObjects();
		
		$i = 0;
		foreach ($trophyList as $trophy) {
			$userIDs = $this->getUserIDs($trophy);
			
			foreach ($userIDs as $userID) {
				(new UserTrophyAction([], 'create', [
					'data' => [
						'trophyID' => $trophy->trophyID,
						'userID' => $userID,
						'time' => TIME_NOW
					]
				]))->executeAction();
				
				if (++$i >= $maxAssigns) return;
			}
		}
	}
	
	/**
	 * Revoke user trophies which are not longer fulfills the conditions. 
	 * 
	 * @param 	integer		$maxRevokes
	 * @since       5.2
	 */
	public function revokeTrophies($maxRevokes = 500) {
		$trophyList = new TrophyList();
		$trophyList->getConditionBuilder()->add('awardAutomatically = ?', [1]);
		$trophyList->getConditionBuilder()->add('revokeAutomatically = ?', [1]);
		$trophyList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$trophyList->readObjects();
		
		$i = 0;
		foreach ($trophyList as $trophy) {
			$userTrophyIDs = $this->getRevocableUserTrophyIDs($trophy, $maxRevokes - $i);
			
			$i += count($userTrophyIDs);
			
			(new UserTrophyAction($userTrophyIDs, 'delete'))->executeAction();
			
			if ($i >= $maxRevokes) return;
		}
	}
	
	/**
	 * Returns the users who fulfill all conditions of the given trophy.
	 *
	 * @param	Trophy		$trophy
	 * @return	integer[]
	 * @since       5.2
	 */
	private function getUserIDs(Trophy $trophy) {
		$userList = new UserList();
		$userList->sqlConditionJoins .= " LEFT JOIN wcf".WCF_N."_user_option_value user_option_value ON (user_option_value.userID = user_table.userID)";
		
		$conditions = $trophy->getConditions();
		foreach ($conditions as $condition) {
			$condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
		}
		
		// prevent multiple awards from a trophy for a user 
		$userList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_trophy WHERE trophyID IN (?))', [$trophy->trophyID]);
		$userList->readObjectIDs();
		
		return $userList->getObjectIDs();
	}
	
	/**
	 * Returns the userTrophyIDs of the users, which no longer fulfills the trophy conditions. 
	 * 
	 * @param       Trophy          $trophy
	 * @param       integer         $maxTrophyIDs		maximum number of trophies that are processed
	 * @return      integer[]
	 * @since       5.2
	 */
	private function getRevocableUserTrophyIDs(Trophy $trophy, $maxTrophyIDs) {
		// Unfortunately, the condition system does not support negated conditions. 
		// Therefore, we need to build our own SQL query. To get to the conditions
		// that must be fulfilled for earning a specific trophy, we first create
		// a pseudo DBOList element to pass them to the condition handler. Then we
		// extract the condition builder from the object.  
		$pseudoUserList = new UserList();
		
		$conditions = $trophy->getConditions();
		
		// Check if there are conditions for the award of the trophy for the given trophy.
		// If there are no conditions, we simply return an empty list and do not remove any trophy. 
		// A trophy without conditions that is awarded automatically cannot be created by default. 
		if (empty($conditions)) {
			return [];
		}
		
		// Assign the condition to the pseudo DBOList object 
		foreach ($conditions as $condition) {
			$condition->getObjectType()->getProcessor()->addUserCondition($condition, $pseudoUserList);
		}
		
		// Now we create our own query to find out which users no longer meet the conditions. 
		// For this we use a UserList object again and transfer basic data from the pseudo object. 
		$userList = new UserList();
		$userList->sqlOrderBy = $pseudoUserList->sqlOrderBy;
		$userList->sqlLimit = $maxTrophyIDs;
		
		// Now we copy the sql joins from the pseudo object to the new one if a condition
		// has joined a table. 
		$userList->sqlJoins = $pseudoUserList->sqlJoins;
		
		// We joining the user_trophy table to receive the userTrophyID, which should be deleted.
		$userList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_trophy user_trophy ON (user_table.userID = user_trophy.userID)";
		
		// We do not need the complete user object, but only the userTrophyID. 
		// So that the UserList object can also assign the users (which is used
		// as an array index), we also get the userID. 
		$userList->useQualifiedShorthand = false;
		$userList->sqlSelects = "user_trophy.userTrophyID, user_table.userID";
		
		// Now we transfer the old conditions to our new object. To avoid having two WHERE keywords,
		// we deactivate it in the pseudo-object.
		$pseudoUserList->getConditionBuilder()->enableWhereKeyword(false);
		$userList->getConditionBuilder()->add('NOT('. $pseudoUserList->getConditionBuilder() .')', $pseudoUserList->getConditionBuilder()->getParameters());
		
		// In order not to get all users who do not fulfill the conditions (in case of
		// doubt there can be many), we filter for users who have received the trophy. 
		$userList->getConditionBuilder()->add('user_table.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_trophy WHERE trophyID IN (?))', [$trophy->trophyID]);
		
		// Prevents us from getting faulty UserTrophyIDs.
		$userList->getConditionBuilder()->add('user_trophy.trophyID = ?', [$trophy->trophyID]);
		$userList->readObjects();
		
		// We now return an array of userTrophyIDs instead of user objects to remove them directly via DBOAction. 
		return array_map(function($object) {
			return $object->userTrophyID; 
		}, $userList->getObjects());
	}
}
