<?php
namespace wcf\data\user\object\watch;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a watched object.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Object\Watch
 *
 * @property-read	integer		$watchID		unique id of the watched object
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.user.objectWatch` object type
 * @property-read	integer		$objectID		id of the watched object of the specific object type
 * @property-read	integer		$userID			id of the user watching the object
 * @property-read	integer		$notification		is `1` if the user wants to receive notifications for the watched object, otherwise `0`
 */
class UserObjectWatch extends DatabaseObject {
	/**
	 * Returns the UserObjectWatch with the given data or null if no such object
	 * exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$userID
	 * @param	integer		$objectID
	 * @return	UserObjectWatch
	 */
	public static function getUserObjectWatch($objectTypeID, $userID, $objectID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_object_watch
			WHERE	objectTypeID = ?
				AND userID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectTypeID, $userID, $objectID]);
		$row = $statement->fetch();
		if (!$row) return null;
		return new UserObjectWatch(null, $row);
	}
}
