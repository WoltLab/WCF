<?php
namespace wcf\data\moderation\queue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\request\LinkHandler;
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
	 * moderation queue editor object
	 * @var	\wcf\data\moderation\queue\ModerationQueueEditor
	 */
	public $moderationQueueEditor = null;
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
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
		$queueList->getConditionBuilder()->add("moderation_queue.status IN (?)", array(array(ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING)));
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
	
	/**
	 * Validates parameters to show the user assign form.
	 */
	public function validateGetAssignUserForm() {
		$this->moderationQueueEditor = $this->getSingleObject();
		
		// check if queue is accessible for current user
		if (!$this->moderationQueueEditor->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns the user assign form.
	 * 
	 * @return	array<string>
	 */
	public function getAssignUserForm() {
		$assignedUser = ($this->moderationQueueEditor->assignedUserID) ? new User($this->moderationQueueEditor->assignedUserID) : null;
		
		WCF::getTPL()->assign(array(
			'assignedUser' => $assignedUser,
			'queue' => $this->moderationQueueEditor
		));
		
		return array(
			'template' => WCF::getTPL()->fetch('moderationQueueAssignUser')
		);
	}
	
	/**
	 * Validates parameters to assign a user.
	 */
	public function validateAssignUser() {
		$this->moderationQueueEditor = $this->getSingleObject();
		$this->readInteger('assignedUserID', true);
		
		if ($this->parameters['assignedUserID'] && $this->parameters['assignedUserID'] != -1) {
			if ($this->parameters['assignedUserID'] != WCF::getUser()->userID && $this->parameters['assignedUserID'] != $this->moderationQueueEditor->assignedUserID) {
				// user id is either faked or changed during viewing, use database value instead
				$this->parameters['assignedUserID'] = $this->moderationQueueEditor->assignedUserID;
			}
		}
		
		if ($this->parameters['assignedUserID'] == -1) {
			$this->readString('assignedUsername');
				
			$this->user = User::getUserByUsername($this->parameters['assignedUsername']);
			if (!$this->user->userID) {
				throw new UserInputException('assignedUsername', 'notFound');
			}
				
			// get handler
			$objectType = ObjectTypeCache::getInstance()->getObjectType($this->moderationQueueEditor->objectTypeID);
			if (!$objectType->getProcessor()->isAffectedUser($this->moderationQueueEditor->getDecoratedObject(), $this->user->userID)) {
				throw new UserInputException('assignedUsername', 'notAffected');
			}
			
			$this->parameters['assignedUserID'] = $this->user->userID;
			$this->parameters['assignedUsername'] = '';
		}
		else {
			$this->user = new User($this->parameters['assignedUserID']);
		}
	}
	
	/**
	 * Returns the data for the newly assigned user.
	 * 
	 * @return	array<string>
	 */
	public function assignUser() {
		$data = array('assignedUserID' => ($this->parameters['assignedUserID'] ?: null));
		if ($this->user->userID) {
			if ($this->moderationQueueEditor->status == ModerationQueue::STATUS_OUTSTANDING) {
				$data['status'] = ModerationQueue::STATUS_PROCESSING;
			}
		}
		else {
			if ($this->moderationQueueEditor->status == ModerationQueue::STATUS_PROCESSING) {
				$data['status'] = ModerationQueue::STATUS_OUTSTANDING;
			}
		}
		
		$this->moderationQueueEditor->update($data);
		
		$username = ($this->user->userID) ? $this->user->username : WCF::getLanguage()->get('wcf.moderation.assignedUser.nobody');
		$link = '';
		if ($this->user->userID) {
			$link = LinkHandler::getInstance()->getLink('User', array('object' => $this->user));
		}
		
		$newStatus = '';
		if (isset($data['status'])) {
			$newStatus = ($data['status'] == ModerationQueue::STATUS_OUTSTANDING) ? 'outstanding' : 'processing';
		}
		
		return array(
			'link' => $link,
			'newStatus' => $newStatus,
			'userID' => $this->user->userID,
			'username' => $username
		);
	}
}
