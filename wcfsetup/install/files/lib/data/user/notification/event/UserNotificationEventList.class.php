<?php
namespace wcf\data\user\notification\event;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification.event
 * @category	Community Framework
 *
 * @method	UserNotificationEvent		current()
 * @method	UserNotificationEvent[]		getObjects()
 * @method	UserNotificationEvent|null	search($objectID)
 * @property	UserNotificationEvent[]		$objects
 */
class UserNotificationEventList extends DatabaseObjectList { }
