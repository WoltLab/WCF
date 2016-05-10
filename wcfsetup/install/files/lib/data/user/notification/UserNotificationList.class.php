<?php
namespace wcf\data\user\notification;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification
 * @category	Community Framework
 *
 * @method	UserNotification		current()
 * @method	UserNotification[]		getObjects()
 * @method	UserNotification|null		search($objectID)
 * @property	UserNotification[]		$objects
 */
class UserNotificationList extends DatabaseObjectList { }
