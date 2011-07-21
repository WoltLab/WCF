<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\IDatabaseObjectProcessor;

/**
 * This interface defines the basic methods every notification object type should implement.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.object.type
 * @category 	Community Framework
 */
interface IUserNotificationObjectType extends IDatabaseObjectProcessor {
	/**
	 * Gets a notification object by its ID.
	 *
	 * @param	integer		$objectID
	 * @return	wcf\system\user\notification\object\IUserNotificationObject
	 */
	public function getObjectByID($objectID);

	/**
	 * Gets notification objects by their IDs.
	 *
	 * @param	array<integer>		$objectIDs
	 * @return	array<wcf\system\user\notification\object\IUserNotificationObject>
	 */
	public function getObjectsByIDs($objectIDs);

}
