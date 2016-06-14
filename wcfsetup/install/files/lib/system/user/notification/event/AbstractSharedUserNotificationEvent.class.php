<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Provides a default implementation for objects sharing common data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 */
abstract class AbstractSharedUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	public function setObject(UserNotification $notification, IUserNotificationObject $object, UserProfile $author, array $additionalData = []) {
		parent::setObject($notification, $object, $author, $additionalData);
		
		$this->prepare();
	}
	
	/**
	 * Provide specialized handlers with object ids, these ids will be collected and should be
	 * read once the first time data is requested from the notification event.
	 */
	abstract protected function prepare();
}
