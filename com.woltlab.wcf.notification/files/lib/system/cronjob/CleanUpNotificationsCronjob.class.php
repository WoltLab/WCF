<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationEditor.class.php');

/**
 * Clears outdated notifications
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CleanUpNotificationsCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		if (!MODULE_USER_NOTIFICATION) return;

		if (USER_NOTIFICATION_LIFETIME > -1) {
			$sql = "SELECT          notificationID
				FROM            wcf".WCF_N."_user_notification
				WHERE		confirmationTime < ".(TIME_NOW - 3600 * USER_NOTIFICATION_LIFETIME)."
				AND             confirmationTime <> 0
				AND             confirmed = 1";
			$result = WCF::getDB()->sendQuery($sql);

			$notificationIDArray = array();
			while ($row = WCF::getDB()->fetchArray($result)) {
				$notificationIDArray[] = $row['notificationID'];
			}

			if (count($notificationIDArray)) {
				NotificationEditor::deleteAll($notificationIDArray);
			}
		}

		if (USER_NOTIFICATION_LIFETIME_UNCONFIRMED > 0) {
			// get affected users
			$sql = "SELECT          user_notification.notificationID, user_notification.userID
				FROM            wcf".WCF_N."_user_notification user_notification
				WHERE		time < ".(TIME_NOW - 3600 * USER_NOTIFICATION_LIFETIME_UNCONFIRMED)."
				AND		confirmed = 0";
			$result = WCF::getDB()->sendQuery($sql);

			$userIDs = array();
			$notificationIDArray = array();
			while ($row = WCF::getDB()->fetchArray($result)) {
				$notificationIDArray[] = $row['notificationID'];
				if ($row['userID'] && !isset($userIDs[$row['userID']])) {
					$userIDs[$row['userID']] = $row['userID'];
				}
			}

			if (count($notificationIDArray)) {
				NotificationEditor::deleteAll($notificationIDArray);
			}

			// update affected users
			NotificationUser::recalculateUserNotificationFlags($userIDs);
		}

		// optimize tables to save some memory (mysql only)
		if (WCF::getDB()->getDBType() == 'MySQLDatabase' || WCF::getDB()->getDBType() == 'MySQLiDatabase' || WCF::getDB()->getDBType() == 'MySQLPDODatabase') {
			$sql = "OPTIMIZE TABLE	wcf".WCF_N."_user_notification, wcf".WCF_N."_user_notification_message";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
}
?>