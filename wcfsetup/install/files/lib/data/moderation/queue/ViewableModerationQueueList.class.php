<?php
namespace wcf\data\moderation\queue;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * Represents a viewable list of moderation queue entries.
 * 
 * WARNING: This database object list uses the moderation_queue_to_user table as primary
 * 	    table and uses a full join for moderation_queue, otherwise the LEFT JOIN
 * 	    would not work (MySQL is retarded).
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 *
 * @method	ViewableModerationQueue		current()
 * @method	ViewableModerationQueue[]	getObjects()
 * @method	ViewableModerationQueue|null	search($objectID)
 * @property	ViewableModerationQueue[]	$objects
 */
class ViewableModerationQueueList extends ModerationQueueList {
	/**
	 * true, if objects should be populated with associated user profiles
	 * @var	boolean
	 */
	public $loadUserProfiles = false;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableModerationQueue::class;
	
	/**
	 * @inheritDoc
	 */
	public $useQualifiedShorthand = false;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects = "moderation_queue.*, assigned_user.username AS assignedUsername, user_table.username";
		$this->sqlConditionJoins = ", wcf".WCF_N."_moderation_queue moderation_queue";
		$this->sqlJoins = ", wcf".WCF_N."_moderation_queue moderation_queue";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user assigned_user ON (assigned_user.userID = moderation_queue.assignedUserID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = moderation_queue.userID)";
		$this->getConditionBuilder()->add("moderation_queue_to_user.queueID = moderation_queue.queueID");
		$this->getConditionBuilder()->add("moderation_queue_to_user.userID = ?", [WCF::getUser()->userID]);
		$this->getConditionBuilder()->add("moderation_queue_to_user.isAffected = ?", [1]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		if (!empty($this->objects)) {
			$objects = [];
			foreach ($this->objects as &$object) {
				if (!isset($objects[$object->objectTypeID])) {
					$objects[$object->objectTypeID] = [];
				}
				
				$objects[$object->objectTypeID][] = $object;
			}
			
			foreach ($objects as $objectTypeID => $queueItems) {
				ModerationQueueManager::getInstance()->populate($objectTypeID, $queueItems);
			}
			
			// check for non-existant items
			$queueIDs = [];
			foreach ($this->objects as $index => $object) {
				if ($object->isOrphaned()) {
					$queueIDs[] = $object->queueID;
					unset($this->objects[$index]);
				}
			}
			
			// remove orphaned queues
			if (!empty($queueIDs)) {
				$this->indexToObject = array_keys($this->objects);
				
				ModerationQueueManager::getInstance()->removeOrphans($queueIDs);
			}
			
			if ($this->loadUserProfiles) {
				$userIDs = [];
				foreach ($this->objects as $object) {
					$userIDs[] = $object->getAffectedObject()->getUserID();
				}
				
				UserProfileRuntimeCache::getInstance()->cacheObjectIDs(array_unique($userIDs));
			}
		}
	}
	
	/**
	 * Returns the name of the database table.
	 * 
	 * @return	string
	 */
	public function getDatabaseTableName() {
		return parent::getDatabaseTableName() . '_to_user';
	}
	
	/**
	 * Returns the name of the database table alias.
	 * 
	 * @return	string
	 */
	public function getDatabaseTableAlias() {
		return parent::getDatabaseTableAlias() . '_to_user';
	}
}
