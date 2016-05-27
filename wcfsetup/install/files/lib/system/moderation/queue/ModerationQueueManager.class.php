<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides methods to manage moderated content and reports.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueManager extends SingletonFactory {
	/**
	 * list of definition names by definition id
	 * @var	string[]
	 */
	protected $definitions = [];
	
	/**
	 * list of moderation types
	 * @var	ObjectType[]
	 */
	protected $moderationTypes = [];
	
	/**
	 * list of object type names categorized by type
	 * @var	integer[][]
	 */
	protected $objectTypeNames = [];
	
	/**
	 * list of object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$moderationTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.moderation.type');
		if (empty($moderationTypes)) {
			throw new SystemException("There are no registered moderation types");
		}
		
		foreach ($moderationTypes as $moderationType) {
			$this->moderationTypes[$moderationType->objectType] = $moderationType;
			
			$definition = ObjectTypeCache::getInstance()->getDefinitionByName($moderationType->objectType);
			if ($definition === null) {
				throw new SystemException("Could not find corresponding definition for moderation type '".$moderationType->objectType."'");
			}
			
			$this->definitions[$definition->definitionID] = $definition->definitionName;
			$this->objectTypeNames[$definition->definitionName] = [];
			
			$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes($definition->definitionName);
			foreach ($objectTypes as $objectType) {
				$this->objectTypeNames[$definition->definitionName][$objectType->objectType] = $objectType->objectTypeID;
				$this->objectTypes[$objectType->objectTypeID] = $objectType;
			}
		}
	}
	
	/**
	 * Returns true if the given combination of definition and object type is valid.
	 * 
	 * @param	string		$definitionName
	 * @param	string		$objectType
	 * @return	boolean
	 */
	public function isValid($definitionName, $objectType) {
		if (!isset($this->objectTypeNames[$definitionName])) {
			return false;
		}
		else if (!isset($this->objectTypeNames[$definitionName][$objectType])) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the object type processor.
	 * 
	 * @param	string		$definitionName
	 * @param	string		$objectType
	 * @param	integer		$objectTypeID
	 * @return	object
	 */
	public function getProcessor($definitionName, $objectType, $objectTypeID = null) {
		if ($objectType !== null) {
			$objectTypeID = $this->getObjectTypeID($definitionName, $objectType);
		}
		
		if ($objectTypeID !== null && isset($this->objectTypes[$objectTypeID])) {
			return $this->objectTypes[$objectTypeID]->getProcessor();
		}
		
		return null;
	}
	
	/**
	 * Returns link for viewing/editing an object type.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$queueID
	 * @return	string
	 */
	public function getLink($objectTypeID, $queueID) {
		foreach ($this->objectTypeNames as $definitionName => $objectTypeIDs) {
			if (in_array($objectTypeID, $objectTypeIDs)) {
				return $this->moderationTypes[$definitionName]->getProcessor()->getLink($queueID);
			}
		}
		
		return '';
	}
	
	/**
	 * Returns object type id.
	 * 
	 * @param	string		$definitionName
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($definitionName, $objectType) {
		if ($this->isValid($definitionName, $objectType)) {
			return $this->objectTypeNames[$definitionName][$objectType];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of moderation types.
	 * 
	 * @return	string[]
	 */
	public function getModerationTypes() {
		return array_keys($this->objectTypeNames);
	}
	
	/**
	 * Returns a list of available definitions.
	 * 
	 * @return	string[]
	 */
	public function getDefinitions() {
		return $this->definitions;
	}
	
	/**
	 * Returns a list of object type ids for given definiton ids.
	 * 
	 * @param	integer[]		$definitionIDs
	 * @return	integer[]
	 */
	public function getObjectTypeIDs(array $definitionIDs) {
		$objectTypeIDs = [];
		foreach ($definitionIDs as $definitionID) {
			if (isset($this->definitions[$definitionID])) {
				foreach ($this->objectTypeNames[$this->definitions[$definitionID]] as $objectTypeID) {
					$objectTypeIDs[] = $objectTypeID;
				}
			}
		}
		
		return $objectTypeIDs;
	}
	
	/**
	 * Populates object properties for viewing.
	 * 
	 * @param	integer				$objectTypeID
	 * @param	ViewableModerationQueue[]	$objects
	 * @throws	SystemException
	 */
	public function populate($objectTypeID, array $objects) {
		$moderationType = '';
		foreach ($this->objectTypeNames as $definitionName => $data) {
			if (in_array($objectTypeID, $data)) {
				$moderationType = $definitionName;
				break;
			}
		}
		
		if (empty($moderationType)) {
			throw new SystemException("Unable to resolve object type id '".$objectTypeID."'");
		}
		
		// forward call to processor
		$this->moderationTypes[$moderationType]->getProcessor()->populate($objectTypeID, $objects);
	}
	
	/**
	 * Returns the count of outstanding moderation queue items.
	 * 
	 * @return	integer
	 */
	public function getOutstandingModerationCount() {
		// get count
		$count = UserStorageHandler::getInstance()->getField('outstandingModerationCount');
		
		// cache does not exist or is outdated
		if ($count === null) {
			// force update of non-tracked queues for this user
			$this->forceUserAssignment();
			
			// count outstanding and assigned queues
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("moderation_queue_to_user.userID = ?", [WCF::getUser()->userID]);
			$conditions->add("moderation_queue_to_user.isAffected = ?", [1]);
			$conditions->add("moderation_queue.status IN (?)", [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]);
			
			$sql = "SELECT		COUNT(*)
				FROM		wcf".WCF_N."_moderation_queue_to_user moderation_queue_to_user
				LEFT JOIN	wcf".WCF_N."_moderation_queue moderation_queue
				ON		(moderation_queue.queueID = moderation_queue_to_user.queueID)
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$count = $statement->fetchSingleColumn();
			
			// update storage data
			UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'outstandingModerationCount', $count);
		}
		
		return $count;
	}
	
	/**
	 * Returns the count of unread moderation queue items.
	 * 
	 * @param	boolean		$skipCache
	 * @return	integer
	 */
	public function getUnreadModerationCount($skipCache = false) {
		// get count
		$count = UserStorageHandler::getInstance()->getField('unreadModerationCount');
		
		// cache does not exist or is outdated
		if ($count === null || $skipCache) {
			// force update of non-tracked queues for this user
			$this->forceUserAssignment();
			
			// count outstanding and assigned queues
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("moderation_queue_to_user.userID = ?", [WCF::getUser()->userID]);
			$conditions->add("moderation_queue_to_user.isAffected = ?", [1]);
			$conditions->add("moderation_queue.status IN (?)", [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]);
			$conditions->add("moderation_queue.time > ?", [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.moderation.queue')]);
			$conditions->add("(moderation_queue.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
			
			$sql = "SELECT		COUNT(*)
				FROM		wcf".WCF_N."_moderation_queue_to_user moderation_queue_to_user
				LEFT JOIN	wcf".WCF_N."_moderation_queue moderation_queue
				ON		(moderation_queue.queueID = moderation_queue_to_user.queueID)
				LEFT JOIN	wcf".WCF_N."_tracked_visit tracked_visit
				ON		(tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue')." AND tracked_visit.objectID = moderation_queue.queueID AND tracked_visit.userID = ".WCF::getUser()->userID.")
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$count = $statement->fetchSingleColumn();
			
			// update storage data
			UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'unreadModerationCount', $count);
		}
		
		return $count;
	}
	
	/**
	 * Forces the update of non-tracked queues for this user.
	 */
	protected function forceUserAssignment() {
		$queueList = new ModerationQueueList();
		$queueList->sqlJoins = "LEFT JOIN wcf".WCF_N."_moderation_queue_to_user moderation_queue_to_user ON (moderation_queue_to_user.queueID = moderation_queue.queueID AND moderation_queue_to_user.userID = ".WCF::getUser()->userID.")";
		$queueList->getConditionBuilder()->add("moderation_queue_to_user.queueID IS NULL");
		$queueList->readObjects();
		
		if (count($queueList)) {
			$queues = [];
			foreach ($queueList as $queue) {
				if (!isset($queues[$queue->objectTypeID])) {
					$queues[$queue->objectTypeID] = [];
				}
				
				$queues[$queue->objectTypeID][$queue->queueID] = $queue;
			}
			
			foreach ($this->objectTypeNames as $definitionName => $objectTypeIDs) {
				foreach ($objectTypeIDs as $objectTypeID) {
					if (isset($queues[$objectTypeID])) {
						$this->moderationTypes[$definitionName]->getProcessor()->assignQueues($objectTypeID, $queues[$objectTypeID]);
					}
				}
			}
		}
	}
	
	/**
	 * Saves moderation queue assignments.
	 * 
	 * @param	boolean[]	$assignments
	 */
	public function setAssignment(array $assignments) {
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_moderation_queue_to_user
						(queueID, userID, isAffected)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($assignments as $queueID => $isAffected) {
			$statement->execute([
				$queueID,
				WCF::getUser()->userID,
				($isAffected ? 1 : 0)
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Identifies and removes orphaned queues.
	 */
	public function identifyOrphans() {
		$sql = "SELECT		moderation_queue.queueID, moderation_queue.objectTypeID, moderation_queue.objectID
			FROM		wcf".WCF_N."_moderation_queue_to_user moderation_queue_to_user
			LEFT JOIN	wcf".WCF_N."_moderation_queue moderation_queue
			ON		(moderation_queue.queueID = moderation_queue_to_user.queueID)
			WHERE		moderation_queue_to_user.userID = ?
					AND moderation_queue_to_user.isAffected = ?
					AND moderation_queue.status <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			WCF::getUser()->userID,
			1,
			ModerationQueue::STATUS_DONE
		]);
		
		$queues = [];
		while ($row = $statement->fetchArray()) {
			$objectTypeID = $row['objectTypeID'];
			if (!isset($queues[$objectTypeID])) {
				$queues[$objectTypeID] = [];
			}
			
			$queues[$objectTypeID][$row['objectID']] = $row['queueID'];
		}
		
		if (!empty($queues)) {
			$queueIDs = [];
			foreach ($queues as $objectTypeID => $objectQueues) {
				$queueIDs = array_merge($queueIDs, $this->getProcessor($this->definitions[$this->objectTypes[$objectTypeID]->definitionID], null, $objectTypeID)->identifyOrphans($objectQueues));
			}
			
			$this->removeOrphans($queueIDs);
		}
	}
	
	/**
	 * Removes a list of orphaned queue ids.
	 * 
	 * @param	integer[]		$queueIDs
	 */
	public function removeOrphans(array $queueIDs) {
		if (!empty($queueIDs)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("queueID IN (?)", [$queueIDs]);
			$sql = "DELETE FROM	wcf".WCF_N."_moderation_queue
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			$this->resetModerationCount();
		}
	}
	
	/**
	 * Resets moderation count for all users or optionally only for one user.
	 * 
	 * @param	integer		$userID
	 */
	public function resetModerationCount($userID = null) {
		if ($userID === null) {
			UserStorageHandler::getInstance()->resetAll('outstandingModerationCount');
			UserStorageHandler::getInstance()->resetAll('unreadModerationCount');
		}
		else {
			UserStorageHandler::getInstance()->reset([$userID], 'outstandingModerationCount');
			UserStorageHandler::getInstance()->reset([$userID], 'unreadModerationCount');
		}
	}
	
	/**
	 * Returns a list of object type ids and their parent definition name.
	 * 
	 * @return	string[]
	 */
	public function getDefinitionNamesByObjectTypeIDs() {
		$definitionNames = [];
		foreach ($this->objectTypeNames as $definitionName => $objectTypes) {
			foreach ($objectTypes as $objectTypeID) {
				$definitionNames[$objectTypeID] = $definitionName;
			}
		}
		
		return $definitionNames;
	}
	
	/**
	 * Returns a list of definition names associated with the specified object type.
	 * 
	 * @param	string		$objectType
	 * @return	string[]
	 */
	public function getDefinitionNamesByObjectType($objectType) {
		$definitionNames = [];
		foreach ($this->objectTypeNames as $definitionName => $objectTypes) {
			if (isset($objectTypes[$objectType])) {
				$definitionNames[] = $definitionName;
			}
		}
		
		return $definitionNames;
	}
	
	/**
	 * Removes moderation queues, should only be called if related objects are permanently deleted.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 * @throws	SystemException
	 */
	public function removeQueues($objectType, array $objectIDs) {
		$definitionNames = $this->getDefinitionNamesByObjectType($objectType);
		if (empty($definitionNames)) {
			throw new SystemException("Object type '".$objectType."' is invalid");
		}
		
		foreach ($definitionNames as $definitionName) {
			$this->getProcessor($definitionName, $objectType)->removeQueues($objectIDs);
		}
		
		$this->resetModerationCount();
	}
}
