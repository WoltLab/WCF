<?php
// wcf imports
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationHandler.class.php');

/**
 * Represents a user in the notifications system
 *
 * @author	Oliver Kliebisch
 * @copyright	2009 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	data.user
 * @category 	Community Framework
 */
class NotificationUser extends UserSession {
	/**
	 * True if the user's notification settings should be loaded
	 *
	 * @var         boolean
	 */
	protected $loadSettings = true;

	/**
	 * Constructs a new NotificationUser object
	 *
	 * @param       integer         $userID
	 * @param       mixed           $row
	 * @param       boolean         $loadSettings
	 * @see         UserSession::__construct
	 */
	public function __construct($userID, $row = null, $loadSettings = true) {
		if (is_object($row)) {
			$row = $row->data;
		}
		$this->loadSettings = $loadSettings;

		parent::__construct($userID, $row);
	}

	/**
	 * @see DatabaseObject::__construct
	 */
	protected function handleData($data) {
		if ($data['userID'] && $this->loadSettings) {
			// load notification settings
			$sql = "SELECT  objectType, eventName, notificationType, enabled
				FROM    wcf".WCF_N."_user_notification_event_to_user
				WHERE   userID = ".$data['userID']."
				AND     packageID IN (".NotificationHandler::getAvailablePackageIDs().")";
			$result = WCF::getDB()->sendQuery($sql);

			$data['eventNotificationSettings'] = array();
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data['eventNotificationSettings'][$row['objectType']][$row['eventName']][$row['notificationType']] = $row['enabled'];
			}
		}
		if (isset($data['notificationFlags'])) {
			$data['notificationFlags'] = unserialize($data['notificationFlags']);
		}
		parent::handleData($data);
	}

	/**
	 * Returns an array of notification types which are activated for the given
	 * event for this user
	 *
	 * @param       string          $eventName
	 * @param       string          $objectType
	 * @return      array<mixed>
	 */
	public function getEventNotificationTypes($eventName, $objectType) {
		$notificationTypes = array();
		if (isset($this->data['eventNotificationSettings'][$objectType][$eventName])) {
			foreach ($this->data['eventNotificationSettings'][$objectType][$eventName] as $typeName => $enabled) {
				if ($enabled) {
					$notificationTypeObject = null;
					try {
						$notificationTypeObject = NotificationHandler::getNotificationTypeObject($typeName);
					}
					catch (SystemException $ex) {
						continue;
					}
					$notificationTypes[] = $notificationTypeObject;
				}
			}
		}
		else {
			$objectTypes = NotificationHandler::getAvailableNotificationObjectTypes();
			$notificationTypeObject = null;
			try {
				$notificationTypeObject = NotificationHandler::getNotificationTypeObject($objectTypes[$objectType]['events'][$eventName]->defaultNotificationType);
				$notificationTypes[] = $notificationTypeObject;
			}
			catch (SystemException $ex) {
				// do nothing
			}
		}

		return $notificationTypes;
	}

	/**
	 * Returns true if there are any unseen notifications for this user
	 *
	 * @return      integer
	 */
	public function hasOutstandingNotifications() {
		$count = 0;

		foreach (explode(',', NotificationHandler::getAvailablePackageIDs()) as $packageID) {
			if (isset($this->data['notificationFlags'][$packageID]) && $this->data['notificationFlags'][$packageID] > 0) {
				$count += $this->data['notificationFlags'][$packageID];
			}
		}

		return $count;
	}

	/**
	 * Increases the notification counter for a notification object type
	 *
	 * @param       integer         $packageID
	 * @param       integer         $count
	 */
	public function addOutstandingNotification($packageID, $count = 1) {
		// validation
		if ($count < 1) return;

		if (isset($this->data['notificationFlags'][$packageID])) {
			$this->data['notificationFlags'][$packageID] += $count;
		}
		else $this->data['notificationFlags'][$packageID] = $count;

		$editor = $this->getEditor();
		$editor->updateFields(array('notificationFlags' => serialize($this->data['notificationFlags'])));
		Session::resetSessions(array($this->userID), true, false);
	}

	/**
	 * Decreases the notification counter for a notification object type
	 *
	 * @param       integer         $packageID
	 * @param       integer         $count
	 */
	public function removeOutstandingNotification($packageID, $count = 1) {
		// validation
		if ($count < 1) return;

		if (isset($this->data['notificationFlags'][$packageID])) {
			$this->data['notificationFlags'][$packageID] -= $count;
			// "thread" security
			if ($this->data['notificationFlags'][$packageID] <= 0) {
				unset($this->data['notificationFlags'][$packageID]);
			}
		}

		$editor = $this->getEditor();
		$editor->updateFields(array('notificationFlags' => serialize($this->data['notificationFlags'])));
		Session::resetSessions(array($this->userID), true, false);
	}

	/**
	 * Recalculates all notification flags
	 */
	public function recalculateOutstandingNotifications() {
		self::recalculateUserNotificationFlags(array($this->userID));
	}

	/**
	 * Bulk processes a number of users and recalculates their notification flags
	 *
	 * @param       array<integer>          userIDs
	 */
	public static function recalculateUserNotificationFlags($userIDs = array()) {
		if (!count($userIDs)) return;
		$sql = "SELECT 		COUNT(*) AS count, n.packageID, u.userID
			FROM            wcf".WCF_N."_user_notification n
			LEFT JOIN
				wcf".WCF_N."_user_notification_to_user u
				ON n.notificationID = u.notificationID
			WHERE           u.userID IN(".implode(',', $userIDs).")
			AND		u.confirmed = 0
			GROUP BY        n.packageID, u.userID";
		$result = WCF::getDB()->sendQuery($sql);

		$notificationFlags = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$notificationFlags[$row['userID']][$row['packageID']] = $row['count'];
		}

		$inserts = '';
		// add empty data and prepare inserts
		foreach ($userIDs as $userID) {
			if (!isset($notificationFlags[$userID])) {
				$notificationFlags[$userID] = array();
			}

			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$userID.", '".escapeString(serialize($notificationFlags[$userID]))."')";
		}

		if (!empty($inserts)) {
			$sql = "INSERT INTO             wcf".WCF_N."_user
							(userID, notificationFlags)
				VALUES                          ".$inserts."
				ON DUPLICATE KEY UPDATE notificationFlags = VALUES(notificationFlags)";
			WCF::getDB()->sendQuery($sql);
		}

		Session::resetSessions($userIDs, true, false);
	}

	/**
	 * @see User::getEditor()
	 */
	public function getEditor() {
		// saves another query
		require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
		return new UserEditor(null, array_merge(get_object_vars($this), $this->data));
	}
}
?>