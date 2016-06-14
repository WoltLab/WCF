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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
abstract class AbstractModerationQueueManager extends SingletonFactory implements IModerationQueueManager {
	/**
	 * definition name
	 * @var	string
	 */
	protected $definitionName = '';
	
	/**
	 * @inheritDoc
	 */
	public function assignQueues($objectTypeID, array $queues) {
		ModerationQueueManager::getInstance()->getProcessor($this->definitionName, null, $objectTypeID)->assignQueues($queues);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectType, $objectID = null) {
		return ModerationQueueManager::getInstance()->isValid($this->definitionName, $objectType);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeID($objectType) {
		return ModerationQueueManager::getInstance()->getObjectTypeID($this->definitionName, $objectType);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProcessor($objectType, $objectTypeID = null) {
		return ModerationQueueManager::getInstance()->getProcessor($this->definitionName, $objectType, $objectTypeID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate($objectTypeID, array $objects) {
		ModerationQueueManager::getInstance()->getProcessor($this->definitionName, null, $objectTypeID)->populate($objects);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canRemoveContent(ModerationQueue $queue) {
		return $this->getProcessor(null, $queue->objectTypeID)->canRemoveContent($queue);
	}
	
	/**
	 * @inheritDoc
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
	protected function addEntry($objectTypeID, $objectID, $containerID = 0, array $additionalData = []) {
		$sql = "SELECT	queueID
			FROM	wcf".WCF_N."_moderation_queue
			WHERE	objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectTypeID,
			$objectID
		]);
		$row = $statement->fetchArray();
		
		if ($row === false) {
			$objectAction = new ModerationQueueAction([], 'create', [
				'data' => [
					'objectTypeID' => $objectTypeID,
					'objectID' => $objectID,
					'containerID' => $containerID,
					'userID' => (WCF::getUser()->userID ?: null),
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				]
			]);
			$objectAction->executeAction();
		}
		else {
			$objectAction = new ModerationQueueAction([$row['queueID']], 'update', [
				'data' => [
					'status' => ModerationQueue::STATUS_OUTSTANDING,
					'containerID' => $containerID,
					'userID' => (WCF::getUser()->userID ?: null),
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				]
			]);
			$objectAction->executeAction();
		}
		
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks a list of moderation queue entries as done.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer[]	$objectIDs
	 */
	protected function removeEntries($objectTypeID, array $objectIDs) {
		$queueList = new ModerationQueueList();
		$queueList->getConditionBuilder()->add("moderation_queue.objectTypeID = ?", [$objectTypeID]);
		$queueList->getConditionBuilder()->add("moderation_queue.objectID IN (?)", [$objectIDs]);
		$queueList->readObjects();
		
		if (count($queueList)) {
			$objectAction = new ModerationQueueAction($queueList->getObjects(), 'markAsDone');
			$objectAction->executeAction();
		}
	}
}
