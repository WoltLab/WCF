<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;
use wcf\system\exception\SystemException;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for user profiles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
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
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$definitionName
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.report';
	
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$objectType
	 */
	protected $objectType = 'com.woltlab.wcf.user';
	
	/**
	 * list of users
	 * @var	array<\wcf\data\user\User>
	 */
	protected static $users = array();
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::assignQueues()
	 */
	public function assignQueues(array $queues) {
		$assignments = array();
		foreach ($queues as $queue) {
			$assignUser = false;
			if (WCF::getSession()->getPermission('mod.general.canUseModeration')) {
				$assignUser = true;
			}
				
			$assignments[$queue->queueID] = $assignUser;
		}
		
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
		WCF::getTPL()->assign(array(
			'user' => new UserProfile($queue->getAffectedObject())
		));
		
		return WCF::getTPL()->fetch('moderationUser');
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::getReportedObject()
	 */
	public function getReportedObject($objectID) {
		if ($this->isValid($objectID)) {
			return $this->getUser($objectID);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::isValid()
	 */
	public function isValid($objectID) {
		if ($this->getUser($objectID) === null) {
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
		if (!array_key_exists($objectID, self::$users)) {
			self::$users[$objectID] = new User($objectID);
			if (!self::$users[$objectID]->userID) {
				self::$users[$objectID] = null;
			}
		}
		
		return self::$users[$objectID];
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::populate()
	 */
	public function populate(array $queues) {
		$objectIDs = array();
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}
		
		// fetch users
		$userList = new UserList();
		$userList->setObjectIDs($objectIDs);
		$userList->readObjects();
		$users = $userList->getObjects();
		
		foreach ($queues as $object) {
			if (isset($users[$object->objectID])) {
				$object->setAffectedObject($users[$object->objectID]);
			}
			else {
				$object->setIsOrphaned();
			}
		}
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::canRemoveContent()
	 */
	public function canRemoveContent(ModerationQueue $queue) {
		return false;
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::removeContent()
	 */
	public function removeContent(ModerationQueue $queue, $message) {
		throw new SystemException("it's not allowed to delete users using the moderation");
	}
}
