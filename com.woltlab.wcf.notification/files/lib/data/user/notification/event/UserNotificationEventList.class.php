<?php
namespace wcf\data\user\notification\event;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification.event
 * @category 	Community Framework
 */
class UserNotificationEventList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\notification\event\UserNotificationEvent';
}
