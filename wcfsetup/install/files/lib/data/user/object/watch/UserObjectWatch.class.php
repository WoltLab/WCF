<?php
namespace wcf\data\user\object\watch;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a watched object.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.object.watch
 * @category	Community Framework
 *
 * @property-read	integer		$watchID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	integer		$userID
 * @property-read	integer		$notification
 */
class UserObjectWatch extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_object_watch';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'watchID';
	
	/**
	 * Returns the UserObjectWatch with the given data or null if no such object
	 * exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$userID
	 * @param	integer		$objectID
	 * @return	\wcf\data\user\object\watch\UserObjectWatch
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
