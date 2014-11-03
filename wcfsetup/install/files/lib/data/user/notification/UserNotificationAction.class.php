<?php
namespace wcf\data\user\notification;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes user notification-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification
 * @category	Community Framework
 */
class UserNotificationAction extends AbstractDatabaseObjectAction {
	/**
	 * notification object
	 * @var	\wcf\data\user\notification\UserNotification
	 */
	public $notification = null;
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$notification = parent::create();
		
		$sql = "INSERT INTO	wcf".WCF_N."_user_notification_to_user
					(notificationID, userID)
			VALUES		(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$notification->notificationID,
			$notification->userID
		));
		
		return $notification;
	}
	
	/**
	 * Creates a simple notification without stacking support, applies to legacy notifications too.
	 * 
	 * @return	array<array>
	 */
	public function createDefault() {
		foreach ($this->parameters['recipients'] as $recipient) {
			$this->parameters['data']['userID'] = $recipient->userID;
			$this->parameters['data']['userID'] = (($recipient->mailNotificationType == 'none' || $recipient->mailNotificationType == 'instant') ? 1 : 0);
			$notification = $this->create();
			
			$notifications[$recipient->userID] = array(
				'isNew' => true,
				'object' => $notification
			);
		}
		
		// insert author
		$sql = "INSERT INTO	wcf".WCF_N."_user_notification_author
					(notificationID, authorID, time)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($notifications as $notificationData) {
			$statement->execute(array(
				$notificationData['object']->notificationID,
				$this->parameters['authorID'] ?: null,
				TIME_NOW
			));
		}
		WCF::getDB()->commitTransaction();
		
		return $notifications;
	}
	
	/**
	 * Creates a notification or adds another author to an existing one.
	 * 
	 * @return	array<array>
	 */
	public function createStackable() {
		// get existing notifications
		$notificationList = new UserNotificationList();
		$notificationList->getConditionBuilder()->add("eventID = ?", array($this->parameters['data']['eventID']));
		$notificationList->getConditionBuilder()->add("eventHash = ?", array($this->parameters['data']['eventHash']));
		$notificationList->getConditionBuilder()->add("userID IN (?)", array(array_keys($this->parameters['recipients'])));
		$notificationList->getConditionBuilder()->add("confirmed = ?", array(0));
		$notificationList->readObjects();
		$existingNotifications = array();
		foreach ($notificationList as $notification) {
			$existingNotifications[$notification->userID] = $notification;
		}
		
		$notifications = array();
		foreach ($this->parameters['recipients'] as $recipient) {
			$notification = (isset($existingNotifications[$recipient->userID]) ? $existingNotifications[$recipient->userID] : null);
			$isNew = ($notification === null);
			
			if ($notification === null) {
				$this->parameters['data']['userID'] = $recipient->userID;
				$this->parameters['data']['userID'] = (($recipient->mailNotificationType == 'none' || $recipient->mailNotificationType == 'instant') ? 1 : 0);
				$notification = $this->create();
			}
			
			$notifications[$recipient->userID] = array(
				'isNew' => $isNew,
				'object' => $notification
			);
		}
		
		// insert author
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_author
						(notificationID, authorID, time)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($notifications as $notificationData) {
			$statement->execute(array(
				$notificationData['object']->notificationID,
				$this->parameters['authorID'] ?: null,
				TIME_NOW
			));
		}
		WCF::getDB()->commitTransaction();
		
		// update trigger count
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	timesTriggered = timesTriggered + ?,
				guestTimesTriggered = guestTimesTriggered + ?
			WHERE	notificationID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($notifications as $notificationData) {
			$statement->execute(array(
				1,
				$this->parameters['authorID'] ? 0 : 1,
				$notificationData['object']->notificationID
			));
		}
		WCF::getDB()->commitTransaction();
		
		return $notifications;
	}
	
	/**
	 * Validates the 'getOustandingNotifications' action.
	 */
	public function validateGetOutstandingNotifications() {
		// does nothing
	}
	
	/**
	 * Loads user notifications.
	 * 
	 * @return	array<array>
	 */
	public function getOutstandingNotifications() {
		$notifications = UserNotificationHandler::getInstance()->getNotifications();
		WCF::getTPL()->assign(array(
			'notifications' => $notifications
		));
		
		return array(
			'template' => WCF::getTPL()->fetch('notificationListOustanding'),
			'totalCount' => UserNotificationHandler::getInstance()->getNotificationCount(true)
		);
	}
	
	/**
	 * Validates if given notification id is valid for current user.
	 */
	public function validateMarkAsConfirmed() {
		$this->readInteger('notificationID');
		$this->notification = new UserNotification($this->parameters['notificationID']);
		
		if (!$this->notification->notificationID) {
			throw new UserInputException('notificationID');
		}
		else if ($this->notification->userID != WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Marks a notification as confirmed.
	 * 
	 * @return	array
	 */
	public function markAsConfirmed() {
		$notificationEditor = new UserNotificationEditor($this->notification);
		$notificationEditor->markAsConfirmed();
		
		// reset notification count
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
		
		return array(
			'notificationID' => $this->parameters['notificationID'],
			'totalCount' => UserNotificationHandler::getInstance()->getNotificationCount()
		);
	}
	
	/**
	 * Validates parameters to mark all notifications of current user as confirmed.
	 */
	public function validateMarkAllAsConfirmed() { /* does nothing */ }
	
	/**
	 * Marks all notifications of current user as confirmed.
	 */
	public function markAllAsConfirmed() {
		// remove notifications for this user
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	confirmed = ?
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			1,
			WCF::getUser()->userID
		));
		
		// reset notification count
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
	}
}
