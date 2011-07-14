<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/Notification.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationHandler.class.php');
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');

/**
 * Represents a list of notifications
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class NotificationList extends DatabaseObjectList {
	// system
	public $sqlOrderBy = 'notification.time DESC';

	/**
	 * list of notifications
	 *
	 * @var array<Notification>
	 */
	public $notifications = array();

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sqlConditions = $this->sqlConditions;
		if (!empty($sqlConditions)) $sqlConditions .= " AND ";
		$sqlConditions .= "notification.packageID IN(".NotificationHandler::getAvailablePackageIDs().")";
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_notification notification
			LEFT JOIN
				wcf".WCF_N."_user_notification_to_user u
				ON notification.notificationID = u.notificationID
			WHERE ".$sqlConditions;
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}

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
			$row['event'] = clone $notificationObjectTypes[$row['objectType']]['events'][$row['eventName']];
			$this->notifications[] = new Notification(null, $row);
		}

	}

	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->notifications;
	}
}
?>
