<?php
namespace wcf\system\user\notification\type;
use wcf\data\user\notification\recipient\UserNotificationRecipient;
use wcf\data\user\notification\UserNotification;
use wcf\data\IDatabaseObjectProcessor;
use wcf\system\user\notification\event\IUserNotificationEvent;

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
interface IUserNotificationType extends IDatabaseObjectProcessor {
	/**
	 * Sends the notification using this notification transport type.
	 *
	 * @param	wcf\data\user\notification\UserNotification			$notification
	 * @param	wcf\data\user\notification\recipient\UserNotificationRecipient	$user
	 * @param	wcf\system\user\notification\event\IUserNotificationEvent	$event
	 */
	public function send(UserNotification $notification, UserNotificationRecipient $user, IUserNotificationEvent $event);

	/**
	 * Tries to revoke the notification. This might not be applicable for all notification types.
	 *
	 * @param	wcf\data\user\notification\UserNotification			$notification
	 * @param	wcf\data\user\notification\recipient\UserNotificationRecipient	$user
	 * @param	wcf\system\user\notification\event\IUserNotificationEvent		$event
	 */
	public function revoke(UserNotification $notification, UserNotificationRecipient $user, IUserNotificationEvent $event);
}
