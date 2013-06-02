<?php
namespace wcf\system\user\activity\point;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\activity\point\event\UserActivityPointEventAction;
use wcf\data\user\UserProfileAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the user activity point events
 * 
 * @author	Tim Duesterhus, Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.user.activity.point
 * @category	Community Framework
 */
class UserActivityPointHandler extends SingletonFactory {
	/**
	 * list of user activity point object types
	 * @var	array<wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * maps the user activity point object type ids to their object type names
	 * @var	array<string>
	 */
	protected $objectTypeNames = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
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
		
		$objectAction = new UserActivityPointEventAction(array(), 'create', array(
			'data' => array(
				'objectTypeID' => $objectTypeObj->objectTypeID,
				'objectID' => $objectID,
				'userID' => $userID,
				'additionalData' => serialize($additionalData)
			)
		));
		$returnValues = $objectAction->executeAction();
		$event = $returnValues['returnValues'];
		
		$this->updateUser($userID, $objectType);
		
		return $event;
	}
	
	/**
	 * Bulk import for user activity point events.
	 * 
	 * structure of $data:
	 * array(
	 * 	objectID => array(
	 * 		userID => userID,
	 * 		additionalData => mixed (optional)
	 * 	)
	 * )
	 * 
	 * @param	string		$objectType
	 * @param	array<array>	$data
	 */
	public function fireEvents($objectType, array $data) {
		$objectTypeObj = $this->getObjectTypeByName($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_user_activity_point_event
					(objectTypeID, objectID, userID, additionalData)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		$userIDs = array();
		foreach ($data as $objectID => $objectData) {
			$statement->execute(array(
				$objectTypeObj->objectTypeID,
				$objectID,
				$objectData['userID'],
				(isset($objectData['additionalData']) ? serialize($objectData['additionalData']) : '')
			));
			
			$userIDs[] = $objectData['userID'];
		}
		WCF::getDB()->commitTransaction();
		
		$userIDs = array_unique($userIDs);
		$this->updateUsers($userIDs, $objectType);
	}
	
	/**
	 * Removes activity point events.
	 * 
	 * @param	string			$objectType
	 * @param	array<integer>		$objectIDs
	 */
	public function removeEvents($objectType, array $objectIDs) {
		if (empty($objectIDs)) return;
		
		// get and validate object type
		$objectTypeObj = $this->getObjectTypeByName($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
		}
		
		// get user ids
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectTypeObj->objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		$sql = "SELECT	DISTINCT userID
			FROM	wcf".WCF_N."_user_activity_point_event
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$userIDs = array();
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		
		// delete events
		$sql = "DELETE FROM	wcf".WCF_N."_user_activity_point_event
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		if (!empty($userIDs)) {
			$this->updateUsers($userIDs, $objectType);
		}
	}
	
	/**
	 * Returns the user activity point event object type with the given id or
	 * null if no such object tyoe exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	wcf\data\object\type\ObjectType
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
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($objectType) {
		if (isset($this->objectTypes[$objectType])) {
			return $this->objectTypes[$objectType];
		}
		
		return null;
	}
	
	/**
	 * Updates the caches for the given user.
	 * 
	 * @param	integer		$userID
	 * @param	string		$objectType
	 */
	public function updateUser($userID, $objectType) {
		$objectType = $this->getObjectTypeByName($objectType);
		
		// update user_activity_point
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_activity_point
			WHERE	userID = ?
				AND objectTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$userID,
			$objectType->objectTypeID
		));
		$row = $statement->fetchArray();
		
		// update existing entry
		if ($row['count']) {
			$sql = "UPDATE	wcf".WCF_N."_user_activity_point
				SET	activityPoints = activityPoints + ?
				WHERE	userID = ?
					AND objectTypeID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectType->points,
				$userID,
				$objectType->objectTypeID
			));
		}
		else {
			// create new entry
			$sql = "INSERT INTO	wcf".WCF_N."_user_activity_point
						(userID, objectTypeID, activityPoints)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$userID,
				$objectType->objectTypeID,
				$objectType->points
			));
		}
		
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	activityPoints = activityPoints + ?
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectType->points,
			$userID
		));
		
		// update user ranks
		$this->updateUserRanks(array($userID));
	}
	
	/**
	 * Updates activity points for given user ids and object type.
	 * 
	 * @param	array<integer>		$userIDs
	 * @param	string			$objectType
	 */
	public function updateUsers(array $userIDs, $objectType = null) {
		$objectTypes = array();
		if ($objectType === null) {
			$objectTypes = $this->objectTypes;
		}
		else {
			$objectTypeObj = $this->getObjectTypeByName($objectType);
			if ($objectTypeObj === null) {
				throw new SystemException("Object type '".$objectType."' is not valid for object type definition 'com.woltlab.wcf.user.activityPointEvent'");
			}
			$objectTypes[] = $objectTypeObj;
		}
		
		$objectTypeIDs = array();
		foreach ($objectTypes as $objectType) {
			$objectTypeIDs[] = $objectType->objectTypeID;
		}
		
		// remove cached values first
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($userIDs));
		$conditions->add("objectTypeID IN (?)", array($objectTypeIDs));
		$sql = "DELETE FROM	wcf".WCF_N."_user_activity_point
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// update users for every given object type
		WCF::getDB()->beginTransaction();
		foreach ($objectTypes as $objectType) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("userID IN (?)", array($userIDs));
			$conditions->add("objectTypeID = ?", array($objectType->objectTypeID));
			
			$parameters = $conditions->getParameters();
			array_unshift($parameters, $objectType->points);
			
			$sql = "INSERT INTO	wcf".WCF_N."_user_activity_point
						(userID, objectTypeID, activityPoints)
				SELECT		userID, objectTypeID, (COUNT(*) * ?) AS activityPoints
				FROM		wcf".WCF_N."_user_activity_point_event
				".$conditions."
				GROUP BY	userID, objectTypeID";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($parameters);
		}
		WCF::getDB()->commitTransaction();
		
		// update activity points for given user ids
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
	 * Updates the user ranks for the given users.
	 * 
	 * @param	array<integer>		$userIDs
	 */
	protected function updateUserRanks(array $userIDs) {
		$action = new UserProfileAction($userIDs, 'updateUserRank');
		$action->executeAction();
	}
}
