<?php
namespace wcf\data\user\notification\event;
use wcf\data\ProcessibleDatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\user\notification\event\IUserNotificationEvent;

/**
 * Represents a user notification event.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Notification\Event
 *
 * @property-read	integer		$eventID
 * @property-read	integer		$packageID
 * @property-read	string		$eventName
 * @property-read	integer		$objectTypeID
 * @property-read	string		$className
 * @property-read	string		$permissions
 * @property-read	string		$options
 * @property-read	integer		$preset
 * @property-read	string		$presetMailNotificationType
 */
class UserNotificationEvent extends ProcessibleDatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_notification_event';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'eventID';
	
	/**
	 * @inheritDoc
	 */
	protected static $processorInterface = IUserNotificationEvent::class;
}
