<?php
namespace wcf\data\user\notification;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit user notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class UserNotificationEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\UserNotification';
	
	/**
	 * @see EditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$recipientIDs = array();
		if (isset($parameters['recipientIDs']) && is_array($parameters['recipientIDs'])) {
			$recipientIDs = $parameters['recipientIDs'];
			unset($parameters['recipientIDs']);
		}
		
		$notification = parent::create($parameters);
		
		// save recpients
		if (count($recipientIDs)) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_notification_to_user
						(notificationID, userID)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($recipientIDs as $recipientID) {
				$statement->execute(array($notification->notificationID, $recipientID));
			}
		}
		
		return $notification;
	}
}
