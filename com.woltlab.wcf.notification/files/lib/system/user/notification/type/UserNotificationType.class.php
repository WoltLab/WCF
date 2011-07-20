<?php
namespace wcf\system\user\notification\type;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;

/**
 * This interface should be implemented by every user notification type.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.type
 * @category 	Community Framework
 */
interface UserNotificationType {
	/**
	 * Sends the notification using this notification transport type.
	 *
	 * @param	wcf\data\user\notification\UserNotification		$notification
	 * @param	wcf\data\user\User					$user
	 * @param	wcf\data\user\notification\event\UserNotificationEvent	$event
	 */
	public function send(UserNotification $notification, User $user, UserNotificationEvent $event);

	/**
	 * Tries to revoke the notification. This might not be applicable for all notification types.
	 *
	 * @param	wcf\data\user\notification\UserNotification		$notification
	 * @param	wcf\data\user\User					$user
	 * @param	wcf\data\user\notification\event\UserNotificationEvent	$event
	 */
	public function revoke(UserNotification $notification, User $user, UserNotificationEvent $event);
}
