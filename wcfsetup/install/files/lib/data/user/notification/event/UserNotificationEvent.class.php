<?php
namespace wcf\data\user\notification\event;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\ProcessibleDatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\user\notification\event\IUserNotificationEvent;

/**
 * Represents a user notification event.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Notification\Event
 *
 * @property-read	integer		$eventID			unique id of the user notification event
 * @property-read	integer		$packageID			id of the package which delivers the user notification event
 * @property-read	string		$eventName			name and textual identifier (within the object type) of the user notification event
 * @property-read	integer		$objectTypeID			id of the `com.woltlab.wcf.notification.objectType` object type
 * @property-read	string		$className			name of the PHP class implementing `wcf\system\user\notification\event\IUserNotificationEvent`
 * @property-read	string		$permissions			comma separated list of user group permissions of which the active user needs to have at least one to see the user notification event setting
 * @property-read	string		$options			comma separated list of options of which at least one needs to be enabled for the user notification event setting to be shown
 * @property-read	integer		$preset				is `1` if the user notification event is enabled by default otherwise `0`
 * @property-read	string		$presetMailNotificationType	default mail notification type if the user notification event is enabled by default, otherwise empty
 */
class UserNotificationEvent extends ProcessibleDatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @inheritDoc
	 */
	protected static $processorInterface = IUserNotificationEvent::class;
	
	/**
	 * Returns the object type of this event.
	 *
	 * @return	ObjectType
	 * @since 5.3
	 */
	public function getObjectType() {
		return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
	}
}
