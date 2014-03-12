<?php
namespace wcf\data\moderation\queue;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes moderation queue-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\moderation\queue\ModerationQueueEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		if (!isset($this->parameters['data']['lastChangeTime'])) {
			$this->parameters['data']['lastChangeTime'] = TIME_NOW;
		}
		
		return parent::create();
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		if (!isset($this->parameters['data']['lastChangeTime'])) {
			$this->parameters['data']['lastChangeTime'] = TIME_NOW;
		}
		
		parent::update();
	}
	
	/**
	 * Marks a list of objects as done.
	 */
	public function markAsDone() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$queueIDs = array();
		foreach ($this->objects as $queue) {
			$queueIDs[] = $queue->queueID;
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("queueID IN (?)", array($queueIDs));
		
		$sql = "UPDATE	wcf".WCF_N."_moderation_queue
			SET	status = ".ModerationQueue::STATUS_DONE."
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// reset number of active moderation queue items
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Validates parameters to fetch a list of outstanding queues.
	 */
	public function validateGetOutstandingQueues() {
		WCF::getSession()->checkPermissions(array('mod.general.canUseModeration'));
	}
	
	/**
	 * Returns a list of outstanding queues.
	 * 
	 * @return	array<string>
	 */
	public function getOutstandingQueues() {
		$objectTypeIDs = ModerationQueueManager::getInstance()->getObjectTypeIDs(array_keys(ModerationQueueManager::getInstance()->getDefinitions()));
		
		$queueList = new ViewableModerationQueueList();
		$queueList->getConditionBuilder()->add("moderation_queue.objectTypeID IN (?)", array($objectTypeIDs));
		$queueList->getConditionBuilder()->add("moderation_queue.status <> ?", array(ModerationQueue::STATUS_DONE));
		$queueList->sqlOrderBy = 'moderation_queue.lastChangeTime DESC';
		$queueList->sqlLimit = 5;
		$queueList->loadUserProfiles = true;
		$queueList->readObjects();
		
		WCF::getTPL()->assign(array(
			'queues' => $queueList
		));
		
		// check if user storage is outdated
		$totalCount = ModerationQueueManager::getInstance()->getOutstandingModerationCount();
		$count = count($queueList);
		if ($count < 5 && $count < $totalCount) {
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'outstandingModerationCount');
			
			// check for orphaned queues
			$queueCount = ModerationQueueManager::getInstance()->getOutstandingModerationCount();
			if (count($queueList) < $queueCount) {
				ModerationQueueManager::getInstance()->identifyOrphans();
			}
		}
		
		return array(
			'template' => WCF::getTPL()->fetch('moderationQueueList'),
			'totalCount' => $totalCount
		);
	}
}
