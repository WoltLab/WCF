<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationList.class.php');
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');

/**
 * Shows outstanding user messages notifications
 *
 * @author      Oliver Kliebisch
 * @copyright   2009-2010 Oliver Kliebisch
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.community.wcf.user.notification
 * @subpackage  system.event.listener
 * @category    Community Framework
 */
class StructuredTemplateUserMessagesNotificationListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// do nothing for guests and spiders or if module is deactivated
		if (WCF::getUser()->userID == 0 || !MODULE_USER_NOTIFICATION) return;

		$user = new NotificationUser(null, WCF::getUser(), false);
		if (!$user->hasOutstandingNotifications()) return;

		// get outstanding notifications
		$notificationList = new NotificationList();
		$notificationList->sqlSelects .= "notification_message.*,";
		$notificationList->sqlJoins .= "INNER JOIN	wcf".WCF_N."_user_notification_message notification_message
						ON		(notification_message.notificationID = notification.notificationID
						AND		notification_message.notificationType = 'userMessages')";
		$notificationList->sqlConditions = "	u.userID = ".$user->userID."
						AND     u.confirmed = 0";
		$notificationList->readObjects();

		$notifications = $notificationList->getObjects();

		if (!count($notifications)) return;
		WCF::getTPL()->assign('notifications', $notifications);
		WCF::getTPL()->append(array(
			'userMessages' => WCF::getTPL()->fetch('userMessagesNotifications')
		));
	}
}
?>