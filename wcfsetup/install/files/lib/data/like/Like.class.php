<?php
namespace wcf\data\like;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a like of an object.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
class Like extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'like';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'likeID';
	
	/**
	 * like value
	 * @var	integer
	 */
	const LIKE = 1;
	
	/**
	 * dislike value
	 * @var	integer
	 */
	const DISLIKE = -1;
	
	/**
	 * Gets a like by type, object id and user id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @param	integer		$userID
	 * @return	Like
	 */
	public static function getLike($objectTypeID, $objectID, $userID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_like
			WHERE	objectTypeID = ?
				AND objectID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeID,
			$objectID,
			$userID
		));
		
		$row = $statement->fetchArray();
		
		if (!$row) {
			$row = array();
		}
		
		return new Like(null, $row);
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableAlias()
	 */
	public static function getDatabaseTableAlias() {
		return 'like_table';
	}
	
	/**
	 * Returns true, if like value is a like.
	 * 
	 * @return	boolean
	 */
	public function isLike() {
		return ($this->likeValue == self::LIKE);
	}
	
	/**
	 * Returns true, if like value is a dislike.
	 * 
	 * @return	boolean
	 */
	public function isDislike() {
		return ($this->likeValue == self::DISLIKE);
	}
}
