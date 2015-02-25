<?php
namespace wcf\data\user\ignore;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an ignored user.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.ignore
 * @category	Community Framework
 */
class UserIgnore extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_ignore';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'ignoreID';
	
	/**
	 * Returns a UserIgnore object for given ignored user id.
	 * 
	 * @param	integer		$ignoreUserID
	 * @return	\wcf\data\user\ignore\UserIgnore
	 */
	public static function getIgnore($ignoreUserID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_ignore
			WHERE	userID = ?
				AND ignoreUserID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			WCF::getUser()->userID,
			$ignoreUserID
		));
		
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new UserIgnore(null, $row);
	}
}
