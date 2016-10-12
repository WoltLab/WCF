<?php
namespace wcf\data\user\notification;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes user notification-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Notification
 * 
 * @method	UserNotificationEditor[]	getObjects()
 * @method	UserNotificationEditor		getSingleObject()
 */
class UserNotificationAction extends AbstractDatabaseObjectAction {
	/**
	 * notification editor object
	 * @var	UserNotificationEditor
	 */
	public $notificationEditor = null;
	
	/**
	 * @inheritDoc
	 * @return	UserNotification
	 */
	public function create() {
		/** @var UserNotification $notification */
		$notification = parent::create();
		
		$sql = "INSERT INTO	wcf".WCF_N."_user_notification_to_user
					(notificationID, userID)
			VALUES		(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$notification->notificationID,
			$notification->userID
		]);
		
		return $notification;
	}
	
	/**
	 * Creates a simple notification without stacking support, applies to legacy notifications too.
	 * 
	 * @return	mixed[][]
	 */
	public function createDefault() {
		$notifications = [];
		foreach ($this->parameters['recipients'] as $recipient) {
			$this->parameters['data']['userID'] = $recipient->userID;
			$this->parameters['data']['mailNotified'] = (($recipient->mailNotificationType == 'none' || $recipient->mailNotificationType == 'instant') ? 1 : 0);
			$notification = $this->create();
			
			$notifications[$recipient->userID] = [
				'isNew' => true,
				'object' => $notification
			];
		}
		
		// insert author
		$sql = "INSERT INTO	wcf".WCF_N."_user_notification_author
					(notificationID, authorID, time)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($notifications as $notificationData) {
			$statement->execute([
				$notificationData['object']->notificationID,
				$this->parameters['authorID'] ?: null,
				TIME_NOW
			]);
		}
		WCF::getDB()->commitTransaction();
		
		return $notifications;
	}
	
	/**
	 * Creates a notification or adds another author to an existing one.
	 * 
	 * @return	mixed[][]
	 */
	public function createStackable() {
		// get existing notifications
		$notificationList = new UserNotificationList();
		$notificationList->getConditionBuilder()->add("eventID = ?", [$this->parameters['data']['eventID']]);
		$notificationList->getConditionBuilder()->add("eventHash = ?", [$this->parameters['data']['eventHash']]);
		$notificationList->getConditionBuilder()->add("userID IN (?)", [array_keys($this->parameters['recipients'])]);
		$notificationList->getConditionBuilder()->add("confirmTime = ?", [0]);
		$notificationList->readObjects();
		$existingNotifications = [];
		foreach ($notificationList as $notification) {
			$existingNotifications[$notification->userID] = $notification;
		}
		
		$notifications = [];
		foreach ($this->parameters['recipients'] as $recipient) {
			$notification = (isset($existingNotifications[$recipient->userID]) ? $existingNotifications[$recipient->userID] : null);
			$isNew = ($notification === null);
			
			if ($notification === null) {
				$this->parameters['data']['userID'] = $recipient->userID;
				$this->parameters['data']['mailNotified'] = (($recipient->mailNotificationType == 'none' || $recipient->mailNotificationType == 'instant') ? 1 : 0);
				$notification = $this->create();
			}
			
			$notifications[$recipient->userID] = [
				'isNew' => $isNew,
				'object' => $notification
			];
		}
		
		uasort($notifications, function ($a, $b) {
			if ($a['object']->notificationID == $b['object']->notificationID) {
				return 0;
			}
			else if ($a['object']->notificationID < $b['object']->notificationID) {
				return -1;
			}
			
			return 1;
		});
		
		// insert author
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_author
						(notificationID, authorID, time)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($notifications as $notificationData) {
			$statement->execute([
				$notificationData['object']->notificationID,
				$this->parameters['authorID'] ?: null,
				TIME_NOW
			]);
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
			$statement->execute([
				1,
				$this->parameters['authorID'] ? 0 : 1,
				$notificationData['object']->notificationID
			]);
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
	 * @return	mixed[]
	 */
	public function getOutstandingNotifications() {
		$notifications = UserNotificationHandler::getInstance()->getMixedNotifications();
		WCF::getTPL()->assign([
			'notifications' => $notifications
		]);
		
		return [
			'template' => WCF::getTPL()->fetch('notificationListUserPanel'),
			'totalCount' => $notifications['notificationCount']
		];
	}
	
	/**
	 * Validates parameters to mark a notification as confirmed.
	 */
	public function validateMarkAsConfirmed() {
		$this->notificationEditor = $this->getSingleObject();
		if ($this->notificationEditor->userID != WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Marks a notification as confirmed.
	 * 
	 * @return	array
	 */
	public function markAsConfirmed() {
		UserNotificationHandler::getInstance()->markAsConfirmedByID($this->notificationEditor->notificationID);
		
		return [
			'markAsRead' => $this->notificationEditor->notificationID,
			'totalCount' => UserNotificationHandler::getInstance()->getNotificationCount(true)
		];
	}
	
	/**
	 * Validates parameters to mark all notifications of current user as confirmed.
	 */
	public function validateMarkAllAsConfirmed() {
		// does nothing
	}
	
	/**
	 * Marks all notifications of current user as confirmed.
	 * 
	 * @return	boolean[]
	 */
	public function markAllAsConfirmed() {
		// remove notifications for this user
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	confirmTime = ?
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			TIME_NOW,
			WCF::getUser()->userID
		]);
		
		// delete notification_to_user assignments (mimic legacy notification system)
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			WHERE		userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([WCF::getUser()->userID]);
		
		// reset notification count
		UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'userNotificationCount');
		
		return [
			'markAllAsRead' => true
		];
	}
}
