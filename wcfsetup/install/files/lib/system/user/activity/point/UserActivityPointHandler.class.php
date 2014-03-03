<?php
namespace wcf\system\user\activity\point;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfileAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the user activity point events
 * 
 * @author	Tim Duesterhus, Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.activity.point
 * @category	Community Framework
 */
class UserActivityPointHandler extends SingletonFactory {
	/**
	 * list of user activity point object types
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * maps the user activity point object type ids to their object type names
	 * @var	array<string>
	 */
	protected $objectTypeNames = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent');
		
		foreach ($this->objectTypes as $objectType) {
			$this->objectTypeNames[$objectType->objectTypeID] = $objectType->objectType;
		}
	}
	
	/**
	 * Adds a new user activity point event.
	 * 
	 * @param	string			$objectType
	 * @param	integer			$objectID
	 * @param	integer			$userID
	 * @param	array<mixed>		$additionalData
	 */
	public function fireEvent($objectType, $objectID, $userID = null, array $additionalData = array()) {
		$objectTypeObj = $this->getObjectTypeByName($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
		}
		
		if ($userID === null) $userID = WCF::getUser()->userID;
		if (!$userID) throw new SystemException("Cannot fire user activity point events for guests");
		
		// update user_activity_point
		$sql = "INSERT INTO		wcf".WCF_N."_user_activity_point
						(userID, objectTypeID, activityPoints, items)
			VALUES			(?, ?, ?, 1)
			ON DUPLICATE KEY
			UPDATE			activityPoints = activityPoints + VALUES(activityPoints),
						items = items + 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$userID,
			$objectTypeObj->objectTypeID,
			$objectTypeObj->points
		));
		
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	activityPoints = activityPoints + ?
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeObj->points,
			$userID
		));
		
		// update user ranks
		$this->updateUserRanks(array($userID));
	}
	
	/**
	 * Bulk import for user activity point events.
	 * 
	 * structure of $itemsToUser:
	 * array(
	 * 	userID => countOfItems
	 * )
	 * 
	 * @param	string			$objectType
	 * @param	array<integer>		$itemsToUser
	 * @param	boolean			$updateUsers
	 */
	public function fireEvents($objectType, array $itemsToUser, $updateUsers = true) {
		$objectTypeObj = $this->getObjectTypeByName($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
		}
		
		if (empty($itemsToUser)) {
			return;
		}
		
		// update user_activity_point
		$values = '';
		$parameters = $userIDs = array();
		foreach ($itemsToUser as $userID => $items) {
			if (!empty($values)) $values .= ',';
			$values .= '(?, ?, ?, ?)';
			$parameters[] = $userID;
			$parameters[] = $objectTypeObj->objectTypeID;
			$parameters[] = $items * $objectTypeObj->points;
			$parameters[] = $items;
			
			$userIDs[] = $userID;
		}
		
		$sql = "INSERT INTO		wcf".WCF_N."_user_activity_point
						(userID, objectTypeID, activityPoints, items)
			VALUES			".$values."
			ON DUPLICATE KEY
			UPDATE			activityPoints = activityPoints + VALUES(activityPoints),
						items = items + VALUES(items)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($parameters);
		
		// update activity points for given user ids
		if ($updateUsers) {
			$this->updateUsers($userIDs);
		}
	}
	
	/**
	 * Removes activity point events.
	 * 
	 * @param	string			$objectType
	 * @param	array<integer>		$userToItems
	 */
	public function removeEvents($objectType, array $userToItems) {
		if (empty($userToItems)) return;
		
		// get and validate object type
		$objectTypeObj = $this->getObjectTypeByName($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
		}
		
		// remove activity points
		$sql = "UPDATE	wcf".WCF_N."_user_activity_point
			SET	activityPoints = activityPoints - ?,
				items = items - ?
			WHERE	objectTypeID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($userToItems as $userID => $items) {
			$statement->execute(array(
				($items * $objectTypeObj->points),
				$items,
				$objectTypeObj->objectTypeID,
				$userID
			));
		}
		
		// update total activity points per user
		$userIDs = array_keys($userToItems);
		$this->updateUsers($userIDs);
	}
	
	/**
	 * Updates total activity points and ranks for given user ids.
	 * 
	 * @param	array<integer>		$userIDs
	 */
	public function updateUsers(array $userIDs) {
		$userIDs = array_unique($userIDs);
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($userIDs));
		
		$sql = "UPDATE	wcf".WCF_N."_user user_table
			SET	activityPoints = COALESCE((
					SELECT		SUM(activityPoints) AS activityPoints
					FROM		wcf".WCF_N."_user_activity_point
					WHERE		userID = user_table.userID
					GROUP BY	userID
				), 0)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// update user ranks
		$this->updateUserRanks($userIDs);
	}
	
	/**
	 * Resets activity points and items for a given object type.
	 * 
	 * @param	string		$objectType
	 */
	public function reset($objectType) {
		// get and validate object type
		$objectTypeObj = $this->getObjectTypeByName($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
		}
		
		$sql = "UPDATE	wcf".WCF_N."_user_activity_point
			SET	activityPoints = 0,
				items = 0
			WHERE	objectTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectTypeObj->objectTypeID));
	}
	
	/**
	 * Returns the user activity point event object type with the given id or
	 * null if no such object tyoe exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypeNames[$objectTypeID])) {
			return $this->getObjectTypeByName($this->objectTypeNames[$objectTypeID]);
		}
		
		return null;
	}
	
	/**
	 * Returns the user activity point event object type with the given name
	 * or null if no such object type exists.
	 * 
	 * @param	string		$objectType
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($objectType) {
		if (isset($this->objectTypes[$objectType])) {
			return $this->objectTypes[$objectType];
		}
		
		return null;
	}
	
	/**
	 * Updates the user ranks for the given users.
	 * 
	 * @param	array<integer>		$userIDs
	 */
	protected function updateUserRanks(array $userIDs) {
		$action = new UserProfileAction($userIDs, 'updateUserRank');
		$action->executeAction();
	}
}
