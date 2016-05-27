<?php
namespace wcf\data\user\follow;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user's follower.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.follow
 * @category	Community Framework
 *
 * @property-read	integer		$followID
 * @property-read	integer		$userID
 * @property-read	integer		$followUserID
 * @property-read	integer		$time
 */
class UserFollow extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_follow';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'followID';
	
	/**
	 * Retrieves a follower.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$followUserID
	 * @return	\wcf\data\user\follow\UserFollow
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
