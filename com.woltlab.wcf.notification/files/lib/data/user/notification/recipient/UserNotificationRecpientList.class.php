<?php
namespace wcf\data\user\notification\recipient;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\data\user\notification\type\UserNotificationType;
use wcf\data\user\UserList;

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
class UserNotificationRecipientList extends UserList {
	/**
	 * @see wcf\data\DatabaseObjectList\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		if ($this->objectIDs === null) {
			$this->readObjectIDs();
		}
		
		if (!count($this->objectIDs)) {
			return;
		}
		
		// get notification types
		$notificationTypes = array();
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('event_to_user.userID IN (?)', array($this->objectIDs));
		$conditionBuilder->add('event_to_user.enabled = ?', array(1));
		
		$sql = "SELECT		event_to_user.eventID, event_to_user.userID, notification_type.*
			FROM		wcf".WCF_N."_user_notification_event_to_user event_to_user
			LEFT JOIN	wcf".WCF_N."_user_notification_type notification_type
			ON		(notification_type.notificationTypeID = event_to_user.notificationTypeID)
			".$conditionBuilder->__toString();
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$notificationTypes[$row['userID']][$row['eventID']] = new UserNotificationType(null, $row);
		}

		// get users
		$sql = "SELECT	".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
				".$this->getDatabaseTableAlias().".*
			FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
				".$this->sqlJoins."
			WHERE	".$this->getDatabaseTableAlias().".".$this->getDatabaseTableIndexName()." IN (?".str_repeat(',?', count($this->objectIDs)).")
				".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->objectIDs);
		while ($row = $statement->fetchArray()) {
			$row['notificationTypes'] = (isset($notificationTypes[$row['userID']]) ? $notificationTypes[$row['userID']] : array());
			$this->objects[] = new UserNotificationRecipient(null, $row); 
		}
	}
}
