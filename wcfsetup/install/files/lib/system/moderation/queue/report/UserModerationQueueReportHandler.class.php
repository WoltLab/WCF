<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\user\User; 
use wcf\data\user\UserAction; 
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;
use wcf\system\option\user\UserOptionHandler; 
use wcf\data\user\group\UserGroup; 
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractModerationQueueHandler; 
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for user.
 * 
 * @author	Joshua Rüsweg
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
class UserModerationQueueReportHandler extends AbstractModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$className
	 */
	protected $className = 'wcf\data\user\User';
	
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$objectType
	 */
	protected $objectType = 'com.woltlab.wcf.user.user';
	
	/**
	 * list of user
	 * @var	array<\wcf\data\user\User>
	 */
	protected static $user = array();
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::assignQueues()
	 */
	public function assignQueues(array $queues) {
		$assignments = array();
		
		// read comments and responses
		$userIDs = array();
		foreach ($queues as $queue) {
			$userIDs[] = $queue->objectID;
		}
		
		$users = User::getUsers($userIDs);
		
		$orphanedQueueIDs = array();
		foreach ($queues as $queue) {
			$assignUser = false;
			
			if (!isset($users[$queue->objectID]) || $users[$queue->objectID]->hasAdministrativeAccess()) {
				$orphanedQueueIDs[] = $queue->queueID;
				continue;
			}
			
			// check if the moderator may administer the group
			$user = $users[$queue->objectID]; 
			if (WCF::getSession()->getPermission('admin.user.canDeleteUser') && UserGroup::isAccessibleGroup($user->getGroupIDs())) {
				$assignUser = true; 
			}
			
			$assignments[$queue->queueID] = $assignUser;
		}
		
		ModerationQueueManager::getInstance()->removeOrphans($orphanedQueueIDs);
		ModerationQueueManager::getInstance()->setAssignment($assignments);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::canReport()
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::getContainerID()
	 */
	public function getContainerID($objectID) {
		return 0;
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::getReportedContent()
	 */
	public function getReportedContent(ViewableModerationQueue $queue) {
		$optionHandler = new UserOptionHandler(false, '', 'profile');
		$optionHandler->enableEditMode(false);
		$optionHandler->showEmptyOptions(false);
		$optionHandler->setUser($queue->getAffectedObject());
		
		WCF::getTPL()->assign(array(
			'options' => $optionHandler->getOptionTree(),
			'user' => new UserProfile($queue->getAffectedObject()), 
			'userID' => $queue->getAffectedObject()->userID
		));
		
		return WCF::getTPL()->fetch('moderationUser');
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::getReportedObject()
	 */
	public function getReportedObject($objectID) {
		return $this->getUser($objectID);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::isValid()
	 */
	public function isValid($objectID) {
		if ($this->getUser($objectID) === null) {
			return false;
		}
		
		// check if the user has admin rights
		// if so, then the whole thing should be blocked
		$user = $this->getUser($objectID); 
		if ($user->hasAdministrativeAccess()) {
			return false; 
		}
		
		return true;
	}
	
	/**
	 * Returns a user object by user id or null if user id is invalid.
	 * 
	 * @param	integer		$objectID
	 * @return	\wcf\data\user\User
	 */
	protected function getUser($objectID) {
		if (!array_key_exists($objectID, self::$user)) {
			self::$user[$objectID] = new User($objectID);
			if (!self::$user[$objectID]->userID) {
				self::$user[$objectID] = null;
			}
		}
		
		return self::$user[$objectID];
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::populate()
	 */
	public function populate(array $queues) {
		$objectIDs = array();
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}
		
		// fetch user
		$userList = new UserList();
		$userList->getConditionBuilder()->add("user_table.userID IN (?)", array($objectIDs));
		$userList->readObjects();
		$user = $userList->getObjects();
		
		foreach ($queues as $object) {
			if (isset($user[$object->objectID])) {
				$object->setAffectedObject($user[$object->objectID]);
			}
			else {
				$object->setIsOrphaned();
			}
		}
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::removeContent()
	 */
	public function removeContent(ModerationQueue $queue, $message) {
		if ($this->isValid($queue->objectID)) {
			$userAction = new UserAction(array($this->getUser($queue->objectID)), 'delete');
			$userAction->executeAction();
		}
	}
}
