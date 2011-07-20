<?php
namespace wcf\data\user\notification\object\type;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user notification object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class UserNotificationObjectTypeList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\notification\object\type\UserNotificationObjectType';
}
