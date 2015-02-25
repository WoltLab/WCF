<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for moderation queue managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
abstract class AbstractModerationQueueManager extends SingletonFactory implements IModerationQueueManager {
	/**
	 * definition name
	 * @var	string
	 */
	protected $definitionName = '';
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::assignQueues()
	 */
	public function assignQueues($objectTypeID, array $queues) {
		ModerationQueueManager::getInstance()->getProcessor($this->definitionName, null, $objectTypeID)->assignQueues($queues);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::isValid()
	 */
	public function isValid($objectType, $objectID = null) {
		return ModerationQueueManager::getInstance()->isValid($this->definitionName, $objectType);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::getObjectTypeID()
	 */
	public function getObjectTypeID($objectType) {
		return ModerationQueueManager::getInstance()->getObjectTypeID($this->definitionName, $objectType);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::getProcessor()
	 */
	public function getProcessor($objectType, $objectTypeID = null) {
		return ModerationQueueManager::getInstance()->getProcessor($this->definitionName, $objectType, $objectTypeID);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::populate()
	 */
	public function populate($objectTypeID, array $objects) {
		ModerationQueueManager::getInstance()->getProcessor($this->definitionName, null, $objectTypeID)->populate($objects);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::canRemoveContent()
	 */
	public function canRemoveContent(ModerationQueue $queue) {
		return $this->getProcessor(null, $queue->objectTypeID)->canRemoveContent($queue);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::removeContent()
	 */
	public function removeContent(ModerationQueue $queue, $message = '') {
		$this->getProcessor(null, $queue->objectTypeID)->removeContent($queue, $message);
	}
	
	/**
	 * Adds an entry to moderation queue.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @param	integer		$containerID
	 * @param	array		$additionalData
	 */
	protected function addEntry($objectTypeID, $objectID, $containerID = 0, array $additionalData = array()) {
		$sql = "SELECT	queueID
			FROM	wcf".WCF_N."_moderation_queue
			WHERE	objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeID,
			$objectID
		));
		$row = $statement->fetchArray();
		
		if ($row === false) {
			$objectAction = new ModerationQueueAction(array(), 'create', array(
				'data' => array(
					'objectTypeID' => $objectTypeID,
					'objectID' => $objectID,
					'containerID' => $containerID,
					'userID' => (WCF::getUser()->userID ?: null),
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				)
			));
			$objectAction->executeAction();
		}
		else {
			$objectAction = new ModerationQueueAction(array($row['queueID']), 'update', array(
				'data' => array(
					'status' => ModerationQueue::STATUS_OUTSTANDING,
					'containerID' => $containerID,
					'userID' => (WCF::getUser()->userID ?: null),
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				)
			));
			$objectAction->executeAction();
		}
		
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks a list of moderation queue entries as done.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	array<integer>	$objectIDs
	 */
	protected function removeEntries($objectTypeID, array $objectIDs) {
		$queueList = new ModerationQueueList();
		$queueList->getConditionBuilder()->add("moderation_queue.objectTypeID = ?", array($objectTypeID));
		$queueList->getConditionBuilder()->add("moderation_queue.objectID IN (?)", array($objectIDs));
		$queueList->readObjects();
		
		if (count($queueList)) {
			$objectAction = new ModerationQueueAction($queueList->getObjects(), 'markAsDone');
			$objectAction->executeAction();
		}
	}
}
