<?php
namespace wcf\system\user\activity\event;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\activity\event\UserActivityEventAction;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.activity.event
 * @category	Community Framework
 */
class UserActivityEventHandler extends SingletonFactory {
	/**
	 * cached object types
	 * @var	array<wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// load object types
		$cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.recentActivityEvent');
		foreach ($cache as $objectType) {
			$this->objectTypes['names'][$objectType->objectType] = $objectType->objectTypeID;
			$this->objectTypes['objects'][$objectType->objectTypeID] = $objectType;
		}
	}
	
	/**
	 * Returns an object type by id.
	 * 
	 * @param	integer				$objectTypeID
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypes['objects'][$objectTypeID])) {
			return $this->objectTypes['objects'][$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns an object type id by object type name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (isset($this->objectTypes['names'][$objectType])) {
			return $this->objectTypes['names'][$objectType];
		}
		
		return null;
	}
	
	/**
	 * Fires a new activity event.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	integer		$languageID
	 * @param	integer		$userID
	 * @param	integer		$time
	 * @param	array		$additonalData
	 * @return	wcf\data\user\activity\event\UserActivityEvent
	 */
	public function fireEvent($objectType, $objectID, $languageID = null, $userID = null, $time = TIME_NOW, $additonalData = array()) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		if ($objectTypeID === null) {
			throw new SystemException("Unknown recent activity event '".$objectType."'");
		}
		
		if ($userID === null) $userID = WCF::getUser()->userID;
		
		$eventAction = new UserActivityEventAction(array(), 'create', array(
			'data' => array(
				'objectTypeID' => $objectTypeID,
				'objectID' => $objectID,
				'languageID' => $languageID,
				'userID' => $userID,
				'time' => $time,
				'additionalData' => serialize($additonalData)
			)
		));
		$returnValues = $eventAction->executeAction();
		
		return $returnValues['returnValues'];
	}
	
	/**
	 * Removes activity events.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public function removeEvents($objectType, array $objectIDs) {
		if (empty($objectIDs)) return;
		
		$objectTypeID = $this->getObjectTypeID($objectType);
		if ($objectTypeID === null) {
			throw new SystemException("Unknown recent activity event '".$objectType."'");
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_activity_event
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
	
	/**
	 * Validates an event list and removes orphaned events.
	 * 
	 * @param	wcf\data\user\activity\event\ViewableUserActivityEventList	$eventList
	 */
	public static function validateEvents(ViewableUserActivityEventList $eventList) {
		$eventIDs = $eventList->validateEvents();
		
		// remove orphaned event ids
		if (!empty($eventIDs)) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_activity_event
				WHERE		eventID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($eventIDs as $eventID) {
				$statement->execute(array($eventID));
			}
		}
	}
}
