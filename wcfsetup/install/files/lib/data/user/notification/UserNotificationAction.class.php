<?php
namespace wcf\data\user\notification;
use wcf\data\user\notification\UserNotificationEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes user notification-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification
 * @category	Community Framework
 */
class UserNotificationAction extends AbstractDatabaseObjectAction {
	/**
	 * Adds notification recipients.
	 */
	public function addRecipients() {
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_to_user
						(notificationID, userID, mailNotified)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($this->objects as $notification) {
			foreach ($this->parameters['recipients'] as $recipient) {
				$statement->execute(array($notification->notificationID, $recipient->userID, ($recipient->mailNotificationType == 'daily' ? 0 : 1)));
			}
		}
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		// create notification
		$notification = parent::create();
		
		// save recpients
		if (!empty($this->parameters['recipients'])) {
			$action = new UserNotificationAction(array($notification), 'addRecipients', array(
				'recipients' => $this->parameters['recipients']		
			));
			$action->executeAction();
		}
		
		return $notification;
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
		
		$totalCount = UserNotificationHandler::getInstance()->getNotificationCount();
		if (count($notifications) < $totalCount) {
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
		}
		
		return array(
			'template' => WCF::getTPL()->fetch('notificationListOustanding'),
			'totalCount' => $totalCount
		);
	}
	
	/**
	 * Validates if given notification id is valid for current user.
	 */
	public function validateMarkAsConfirmed() {
		$this->readInteger('notificationID');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_notification_to_user
			WHERE	notificationID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->parameters['notificationID'],
			WCF::getUser()->userID
		));
		$row = $statement->fetchArray();
		
		// pretend it was marked as confirmed
		if (!$row['count']) {
			$this->parameters['alreadyConfirmed'] = true;
		}
	}
	
	/**
	 * Marks a notification as confirmed.
	 * 
	 * @return	array
	 */
	public function markAsConfirmed() {
		if (!isset($this->parameters['alreadyConfirmed'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
				WHERE		notificationID = ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->parameters['notificationID'],
				WCF::getUser()->userID
			));
			
			// remove entirely read notifications
			$sql = "SELECT	COUNT(*) as count
				FROM	wcf".WCF_N."_user_notification_to_user
				WHERE	notificationID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->parameters['notificationID']));
			$row = $statement->fetchArray();
			if (!$row['count']) {
				UserNotificationEditor::deleteAll(array($this->parameters['notificationID']));
			}
			
			// reset notification count
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
		}
		
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
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			WHERE		userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(WCF::getUser()->userID));
		
		// reset notification count
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
	}
}
