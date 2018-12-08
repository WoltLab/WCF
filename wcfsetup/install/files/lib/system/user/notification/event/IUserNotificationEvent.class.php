<?php
namespace wcf\system\user\notification\event;
use wcf\data\language\Language;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserProfile;
use wcf\data\IDatabaseObjectProcessor;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * This interface should be implemented by every event which is fired by the notification system.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2018 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * 
 * @mixin	UserNotificationEvent
 */
interface IUserNotificationEvent extends IDatabaseObjectProcessor {
	/**
	 * Returns a short title used for the notification overlay, e.g. "New follower".
	 * 
	 * @return	string
	 */
	public function getTitle();
	
	/**
	 * Returns the notification event message, e.g. "dtdesign is now following you".
	 * 
	 * @return	string
	 */
	public function getMessage();
	
	/**
	 * Returns object link.
	 * 
	 * @return	string
	 */
	public function getLink();
	
	/**
	 * Returns the full title for this notification, e.g. for use with e-mails.
	 * 
	 * @return	string
	 */
	public function getEmailTitle();
	
	/**
	 * Returns the message for this notification event.
	 * 
	 * If $notificationType is 'instant' this method should either:
	 * - Return a string to be inserted into a text/plain email (deprecated)
	 * - Return an ['template' => ...,
	 *             'application' => ...,
	 *             'variables' => ...,
	 *             'message-id' => ...,
	 *             'in-reply-to' => [...],
	 *             'references' => [...]]
	 *   array to be included into the notification email.
	 *   message-id, in-reply-to and references refer to the respective headers
	 *   of an email and are optional. You MUST NOT generate a message-id if you
	 *   cannot ensure that it will *never* repeat.
	 * 
	 * If $notificationType is 'daily' this method should either:
	 * - Return a string to be inserted into the summary email (deprecated)
	 * - Return an ['template' => ..., 'application' => ..., 'variables' => ...] array
	 *   to be included into the summary email.
	 * 
	 * @param	string		$notificationType
	 * @return	mixed
	 * @see		\wcf\system\email\Email
	 */
	public function getEmailMessage($notificationType = 'instant');
	
	/**
	 * Returns the author id for this notification event.
	 * 
	 * @return	integer
	 */
	public function getAuthorID();
	
	/**
	 * Returns the author for this notification event.
	 * 
	 * @return	UserProfile
	 */
	public function getAuthor();
	
	/**
	 * Returns a list of authors for stacked notifications sorted by time.
	 * 
	 * @return	UserProfile[]
	 */
	public function getAuthors();
	
	/**
	 * Returns true if this notification event is visible for the active user.
	 * 
	 * @return	boolean
	 */
	public function isVisible();
	
	/**
	 * Sets a list of authors for stacked notifications.
	 * 
	 * @param	UserProfile[]	$authors
	 */
	public function setAuthors(array $authors);
	
	/**
	 * Returns a unique identifier of the event.
	 * 
	 * @return	string
	 */
	public function getEventHash();
	
	/**
	 * Sets the object for the event.
	 * 
	 * @param	UserNotification		$notification
	 * @param	IUserNotificationObject		$object
	 * @param	UserProfile			$author
	 * @param	array				$additionalData
	 */
	public function setObject(UserNotification $notification, IUserNotificationObject $object, UserProfile $author, array $additionalData = []);
	
	/**
	 * Sets the language for the event
	 * 
	 * @param	Language	$language
	 */
	public function setLanguage(Language $language);
	
	/**
	 * Returns true if this notification event supports stacking.
	 * 
	 * @return	boolean
	 */
	public function isStackable();
	
	/**
	 * Returns true if this notification event supports email notifications.
	 * 
	 * @return	boolean
	 */
	public function supportsEmailNotification();
	
	/**
	 * Validates if the related object is still accessible, in case this check fails
	 * the event should take the appropriate actions to resolve this.
	 * 
	 * @return	boolean
	 */
	public function checkAccess();
	
	/**
	 * Returns true if a notification should be deleted if the related object
	 * is not accessible.
	 * 
	 * @return	boolean
	 */
	public function deleteNoAccessNotification();
	
	/**
	 * Returns true if the underlying notification has been marked as confirmed.
	 * 
	 * @return	boolean
	 */
	public function isConfirmed();
	
	/**
	 * Returns the underlying notification object.
	 * 
	 * @return	UserNotification
	 */
	public function getNotification();
	
	/**
	 * Returns the underlying user notification object.
	 * 
	 * @return	IUserNotificationObject
	 */
	public function getUserNotificationObject();
}
