<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/UserNotification.class.php');
require_once(WCF_DIR.'lib/data/user/notification/type/NotificationType.class.php');
require_once(WCF_DIR.'lib/data/user/notification/object/UserNotificationConveyableObject.class.php');

/**
 * This interface should be implemented by every event which is fired by the notification system
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.event
 * @category 	Community Framework
 */
interface UserNotificationConveyableEvent {
	/**
	 * Callback function for event based data manipulation for notifications
	 *
	 * @param       array<mixed>     $data
	 */
	public function initialize(&$data);

	/**
	 * Returns the message for this notification event.
	 *
	 * @param       NotificationType        $notificationType
	 * @return      string
	 */
	public function getMessage(NotificationType $notificationType);

	/**
	 * Returns the short output for this notification event
	 *
	 * @return      string
	 */
	public function getShortOutput();

	/**
	 * Returns the medium output for this notification event
	 *
	 * @return      string
	 */
	public function getMediumOutput();

	/**
	 * Returns the full output for this notification event
	 *
	 * @return      string
	 */
	public function getOutput();

	/**
	 * Returns the human-readable title of this event
	 *
	 * @return      string
	 */
	public function getTitle();

	/**
	 * Returns the human-readable description of this event
	 *
	 * @return      string
	 */
	public function getDescription();

	/**
	 * Returns the icon of this event
	 *
	 * @return      string
	 */
	public function getIcon();

	/**
	 *
	 * @param       string          $var
	 * @param       array<mixed>    $additionalVariables
	 */
	public function getLanguageVariable($var, $additionalVariables = array());

	/**
	 * Returns true if this event supports the given notification type
	 *
	 * @param       NotificationType        $notificationType
	 * @return      boolean
	 */
	public function supportsNotificationType(NotificationType $notificationType);

	/**
	 * Sets the recipient user's language
	 *
	 * @param       Language        $language
	 */
	public function setLanguage(Language $language);

	/**
	 * Returns the recipient user's language
	 *
	 * @return      Language
	 */
	public function getLanguage();

	/**
	 * Sets the object for the event
	 *
	 * @param       UserNotificationConveyableObject	$object
	 * @param       array<mixed>				$additionalData
	 */
	public function setObject(UserNotificationConveyableObject $object, $additionalData = array());

	/**
	 * Returns the object of this event
	 *
	 * @return      NotificationObject
	 */
	public function getObject();

	/**
	 * Returns the name of this event
	 *
	 * @deprecated
	 * @return      string
	 */
	public function getEventName();

	/**
	 * Returns the URL for accepting the notification
	 *
	 * @param       UserNotification    $notification
	 * @return      string
	 */
	public function getAcceptURL(UserNotification $notification);

	/**
	 * Returns the URL for declining the notification
	 *
	 * @param       UserNotification    $notification
	 * @return      string
	 */
	public function getDeclineURL(UserNotification $notification);
}
?>