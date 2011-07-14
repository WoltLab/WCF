<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/notification/event/UserNotificationConveyableEvent.class.php');
require_once(WCF_DIR.'lib/data/user/notification/UserNotificationEditor.class.php');

/**
 * This interface should be implemented by every notification type
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.type
 * @category 	Community Framework
 */
interface UserNotificationReceivableType {
	/**
	 * Sends the notification using this notification transport type
	 *
	 * @param       User					$user
	 * @param       UserNotificationConveyableEvent		$event
	 * @param       UserNotificationEditor			$notification
	 */
	public function send(User $user, UserNotificationConveyableEvent $event, UserNotificationEditor $notification);

	/**
	 * Tries to revoke the notification. This might not be applicable for
	 * all notification types
	 *
	 * @param       User					$user
	 * @param       UserNotificationConveyableEvent		$event
	 * @param       UserNotificationEditor			$notification
	 */
	public function revoke(User $user, UserNotificationConveyableEvent $event, UserNotificationEditor $notification);

	/**
	 * Returns the name of the notification type
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns the icon of the notification type
	 *
	 * @return string
	 */
	public function getIcon();
}
?>