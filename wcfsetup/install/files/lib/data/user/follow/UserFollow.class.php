<?php
namespace wcf\data\user\follow;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user's follower.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Follow
 *
 * @property-read	integer		$followID		unique id of the following relation
 * @property-read	integer		$userID			id of the following user
 * @property-read	integer		$followUserID		id of the followed user
 * @property-read	integer		$time			time at which following relation has been established
 */
class UserFollow extends DatabaseObject {
	/**
	 * Retrieves a follower.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$followUserID
	 * @return	UserFollow
	 */
	public static function getFollow($userID, $followUserID) {
		$sql = "SELECT	followID
			FROM	wcf".WCF_N."_user_follow
			WHERE	userID = ?
				AND followUserID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$userID,
			$followUserID
		]);
		
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new UserFollow(null, $row);
	}
}
