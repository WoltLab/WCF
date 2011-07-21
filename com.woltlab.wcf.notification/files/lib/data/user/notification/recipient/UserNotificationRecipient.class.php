<?php
namespace wcf\data\user\notification\recipient;
use wcf\data\user\notification\type\UserNotificationType;
use wcf\data\user\User;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Decorates the user object to provide special functions for handling recipients of user notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification.user
 * @category 	Community Framework
 */
class UserNotificationRecipient extends DatabaseObjectDecorator {
	/**
	 * @see	DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\User';
	
	/**
	 * Creates a new UserNotificationRecipient object.
	 * 
	 * @param	wcf\data\user\User		$object
	 */
	public function __construct(User $object) {
		parent::__construct($object);
		
		// get notification types
		if (!isset($this->object->data['notificationTypes'])) {
			$this->object->data['notificationTypes'] = array();
			$sql = "SELECT		event_to_user.eventID, notification_type.*
				FROM		wcf".WCF_N."_user_notification_event_to_user event_to_user
				LEFT JOIN	wcf".WCF_N."_user_notification_type notification_type
				ON		(notification_type.notificationTypeID = event_to_user.notificationTypeID)
				WHERE		event_to_user.userID = ?
						AND event_to_user.enabled = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->userID, 1));
			while ($row = $statement->fetchArray()) {
				$databaseObject = new UserNotificationType(null, $row);
				$this->object->data['notificationTypes'][$row['eventID']][] = $databaseObject->getProcessor();
			}
		}
	}
	
	/**
	 * Returns the enabled notification types for the given event.
	 * 
	 * @param	integer		$eventID
	 * @return	array<wcf\system\user\notification\type\IUserNotificationType>
	 */
	public function getNotificationTypes($eventID) {
		if (isset($this->notificationTypes[$eventID])) {
			return $this->notificationTypes[$eventID];
		}
		
		return array();
	}
}
