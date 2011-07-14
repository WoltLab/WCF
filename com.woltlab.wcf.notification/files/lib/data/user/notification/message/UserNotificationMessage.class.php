<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a notification message.
 *
 * @author	Marcel Werk
 * @copyright	2009-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.message
 * @category 	Community Framework
 */
class UserNotificationMessage extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	public $databaseTableName = 'user_notification_message';
	
	/**
	 * @see	DatabaseObject::$databaseIndexName
	 */
	public $databaseIndexName = 'messageID';
	
	/**
	 * Returns a notification message based on the given parameters.
	 *
	 * @param       integer         $notificationID
	 * @param       string          $notificationType
	 * @return	UserNotificationMessage
	 */
	public static function getMessage($notificationID, $notificationType) {
		$sql = "SELECT	*
			FROM    wcf".WCF_N."_user_notification_message
			WHERE   notificationID = ".$notificationID."
				AND notificationType = '".escapeString($notificationType)."'";
		if (($row = WCF::getDB()->getFirstRow($sql)) === false) {
			$row = array();
		}
		
		return new UserNotificationMessage(null, $row);
	}
}
?>