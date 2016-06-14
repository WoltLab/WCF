<?php
namespace wcf\data\user\notification\event;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Notification\Event
 *
 * @method	UserNotificationEvent		current()
 * @method	UserNotificationEvent[]		getObjects()
 * @method	UserNotificationEvent|null	search($objectID)
 * @property	UserNotificationEvent[]		$objects
 */
class UserNotificationEventList extends DatabaseObjectList { }
