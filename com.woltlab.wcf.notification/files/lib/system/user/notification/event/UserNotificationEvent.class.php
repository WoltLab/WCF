<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\notification\type;

/**
 * This interface should be implemented by every event which is fired by the notification system.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.event
 * @category 	Community Framework
 */
interface UserNotificationEvent {
	/**
	 * Returns the message for this notification event.
	 *
	 * @param	wcf\data\user\notification\type\UserNotificationType	$notificationType
	 * @return	string
	 */
	public function getMessage(UserNotificationType $notificationType);

	/**
	 * Returns the short output for this notification event.
	 *
	 * @return	string
	 */
	public function getShortOutput();

	/**
	 * Returns the medium output for this notification event.
	 *
	 * @return	string
	 */
	public function getMediumOutput();

	/**
	 * Returns the full output for this notification event.
	 *
	 * @return	string
	 */
	public function getOutput();

	/**
	 * Returns the human-readable title of this event.
	 *
	 * @return	string
	 */
	public function getTitle();

	/**
	 * Returns the human-readable description of this event.
	 *
	 * @return	string
	 */
	public function getDescription();

	/**
	 * Returns true if this event supports the given notification type.
	 *
	 * @param	wcf\data\user\notification\type\UserNotificationType	$notificationType
	 * @return	boolean
	 */
	public function supportsNotificationType(UserNotificationType $notificationType);
}
