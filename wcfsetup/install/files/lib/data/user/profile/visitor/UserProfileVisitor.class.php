<?php
namespace wcf\data\user\profile\visitor;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user profile visitor.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.visitor
 * @category	Community Framework
 */
class UserProfileVisitor extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_profile_visitor';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'visitorID';
	
	/**
	 * Gets a profile visitor object.
	 * 
	 * @param	integer		$ownerID
	 * @param	integer		$userID
	 * @return	\wcf\data\user\profile\visitor\UserProfileVisitor
	 */
	public static function getObject($ownerID, $userID) {
		$sql = "SELECT	*
			FROM	".static::getDatabaseTableName()."
			WHERE	ownerID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($ownerID, $userID));
		if ($row = $statement->fetchArray()) {
			return new UserProfileVisitor(null, $row);
		}
		
		return null;
	}
}
