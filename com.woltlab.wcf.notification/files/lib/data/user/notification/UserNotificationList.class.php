<?php
namespace wcf\data\user\notification;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class UserNotificationList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\notification\UserNotification';
}
