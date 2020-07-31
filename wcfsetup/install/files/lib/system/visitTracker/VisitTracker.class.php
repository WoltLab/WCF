<?php
namespace wcf\system\visitTracker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles object visit tracking.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\VisitTracker
 */
class VisitTracker extends SingletonFactory {
	/**
	 * default tracking lifetime
	 * @var	integer
	 */
	const DEFAULT_LIFETIME = 604800; // = one week
	
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = [];
	
	/**
	 * user visits
	 * @var	array
	 */
	protected $userVisits = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.visitTracker.objectType');
	}
	
	/**
	 * Returns the object type id of the given visit tracker object type.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 * @throws	SystemException
	 */
	public function getObjectTypeID($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType]->objectTypeID;
	}
	
	/**
	 * Returns the last visit time for a whole object type.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getVisitTime($objectType) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		if ($this->userVisits === null) {
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('trackedUserVisits');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$sql = "SELECT	objectTypeID, visitTime
						FROM	wcf".WCF_N."_tracked_visit_type
						WHERE	userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([WCF::getUser()->userID]);
					$this->userVisits = $statement->fetchMap('objectTypeID', 'visitTime');
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'trackedUserVisits', serialize($this->userVisits));
				}
				else {
					$this->userVisits = @unserialize($data);
				}
			}
			else {
				$this->userVisits = WCF::getSession()->getVar('trackedUserVisits');
			}
			
			if (!$this->userVisits) {
				$this->userVisits = [];
			}
		}
		
		$lifetime = ($this->availableObjectTypes[$objectType]->lifetime) ?: self::DEFAULT_LIFETIME;
		$minimum = TIME_NOW - $lifetime;
		
		if (isset($this->userVisits[$objectTypeID])) {
			// double times the lifetime period for existing visit data;
			// equals 2 weeks for the default lifetime of 7 days
			$minimum -= $lifetime;
			
			// using `max()` here will yield the most recent point in time
			return max($this->userVisits[$objectTypeID], $minimum);
		}
		
		return $minimum;
	}
	
	/**
	 * Returns the last visit time for a specific object.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @return	integer
	 */
	public function getObjectVisitTime($objectType, $objectID) {
		if (WCF::getUser()->userID) {
			$sql = "SELECT	visitTime
				FROM	wcf".WCF_N."_tracked_visit
				WHERE	objectTypeID = ?
					AND objectID = ?
					AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->getObjectTypeID($objectType), $objectID, WCF::getUser()->userID]);
			$row = $statement->fetchArray();
			if ($row) return $row['visitTime'];
		}
		else {
			if ($visitTime = WCF::getSession()->getVar('trackedUserVisit_'.$this->getObjectTypeID($objectType).'_'.$objectID)) {
				return $visitTime;
			}
		}
		
		return $this->getVisitTime($objectType);
	}
	
	/**
	 * Deletes all tracked visits of a specific object type.
	 * 
	 * @param	string		$objectType
	 */
	public function deleteObjectVisits($objectType) {
		if (WCF::getUser()->userID) {
			$sql = "DELETE FROM	wcf".WCF_N."_tracked_visit
				WHERE		objectTypeID = ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->getObjectTypeID($objectType), WCF::getUser()->userID]);
		}
	}
	
	/**
	 * Tracks an object visit for the users with the given ids.
	 *
	 * @param   string      $objectType
	 * @param   integer     $objectID
	 * @param   integer[]   $userIDs
	 * @param   int         $time
	 */
	public function trackObjectVisitByUserIDs($objectType, $objectID, array $userIDs, $time = TIME_NOW) {
		// save visit
		$sql = "REPLACE INTO wcf".WCF_N."_tracked_visit
					(objectTypeID, objectID, userID, visitTime)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$objectTypeID = $this->getObjectTypeID($objectType);
		WCF::getDB()->beginTransaction();
		
		foreach ($userIDs as $userID) {
			$statement->execute([$objectTypeID, $objectID, $userID, $time]);
		}
		
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Tracks an object visit.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	integer		$time
	 */
	public function trackObjectVisit($objectType, $objectID, $time = TIME_NOW) {
		if (WCF::getUser()->userID) {
			// save visit
			$sql = "REPLACE INTO	wcf".WCF_N."_tracked_visit
						(objectTypeID, objectID, userID, visitTime)
				VALUES		(?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->getObjectTypeID($objectType), $objectID, WCF::getUser()->userID, $time]);
		}
		else if (WCF::getSession()->spiderID === null) {
			WCF::getSession()->register('trackedUserVisit_'.$this->getObjectTypeID($objectType).'_'.$objectID, $time);
		}
	}
	
	/**
	 * Tracks an object type visit.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$time
	 */
	public function trackTypeVisit($objectType, $time = TIME_NOW) {
		if (WCF::getUser()->userID) {
			// save visit
			$sql = "REPLACE INTO	wcf".WCF_N."_tracked_visit_type
						(objectTypeID, userID, visitTime)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->getObjectTypeID($objectType), WCF::getUser()->userID, $time]);
			
			// delete obsolete object visits
			$sql = "DELETE FROM	wcf".WCF_N."_tracked_visit
				WHERE		objectTypeID = ?
						AND userID = ?
						AND visitTime <= ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->getObjectTypeID($objectType), WCF::getUser()->userID, $time]);
			
			// reset storage
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'trackedUserVisits');
		}
		else if (WCF::getSession()->spiderID === null) {
			$this->getVisitTime($objectType);
			$this->userVisits[$this->getObjectTypeID($objectType)] = $time;
			WCF::getSession()->register('trackedUserVisits', $this->userVisits);
		}
	}
}
