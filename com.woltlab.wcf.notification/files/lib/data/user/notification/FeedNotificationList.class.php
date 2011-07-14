<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/NotificationList.class.php');
require_once(WCF_DIR.'lib/data/user/notification/FeedNotification.class.php');

/**
 * Represents a list of feed notifications
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class FeedNotificationList extends NotificationList {
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		// get ids
		$notificationIDArray = $objectIDArray = $objects = array();
		$notificationObjectTypes = NotificationHandler::getAvailableNotificationObjectTypes();
		$sqlConditions = $this->sqlConditions;
		if (!empty($sqlConditions)) $sqlConditions .= " AND ";
		$sqlConditions .= "notification.packageID IN(".NotificationHandler::getAvailablePackageIDs().")";
		$sql = "SELECT		 ".$this->sqlSelects." notification.*, GROUP_CONCAT(u.userID SEPARATOR ',') AS userID
			FROM	wcf".WCF_N."_user_notification notification
			LEFT JOIN
				wcf".WCF_N."_user_notification_to_user u
				ON notification.notificationID = u.notificationID
			".$this->sqlJoins."
			WHERE ".$sqlConditions."
			GROUP BY notification.notificationID
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray()) {
			if (!isset($notificationObjectTypes[$row['objectType']]['events'][$row['eventName']])) continue;
			$row['event'] = $notificationObjectTypes[$row['objectType']]['events'][$row['eventName']];
			$this->notifications[] = new FeedNotification(null, $row);
		}

	}
}
?>