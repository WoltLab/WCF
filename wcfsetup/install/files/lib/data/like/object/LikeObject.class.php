<?php
namespace wcf\data\like\object;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a liked object.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like.object
 * @category	Community Framework
 *
 * @property-read	integer		$likeObjectID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	integer|null	$objectUserID
 * @property-read	integer		$likes
 * @property-read	integer		$dislikes
 * @property-read	integer		$cumulativeLikes
 * @property-read	string		$cachedUsers
 */
class LikeObject extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'like_object';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'likeObjectID';
	
	/**
	 * liked object
	 * @var	ILikeObject
	 */
	protected $likedObject = null;
	
	/**
	 * list of users who liked this object
	 * @var	User[]
	 */
	protected $users = array();
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// get user objects from cache
		if (!empty($data['cachedUsers'])) {
			$cachedUsers = @unserialize($data['cachedUsers']);
			
			if (is_array($cachedUsers)) {
				foreach ($cachedUsers as $cachedUserData) {
					$user = new User(null, $cachedUserData);
					$this->users[$user->userID] = $user;
				}
			}
		}
	}
	
	/**
	 * Gets the first 3 users who liked this object.
	 * 
	 * @return	User[]
	 */
	public function getUsers() {
		return $this->users;
	}
	
	/**
	 * Returns the liked object.
	 * 
	 * @return	ILikeObject
	 */
	public function getLikedObject() {
		if ($this->likedObject === null) {
			$this->likedObject = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->getProcessor()->getObjectByID($this->objectID);
		}
		
		return $this->likedObject;
	}
	
	/**
	 * Sets the liked object.
	 * 
	 * @param	ILikeObject	$likedObject
	 */
	public function setLikedObject(ILikeObject $likedObject) {
		$this->likedObject = $likedObject;
	}
	
	/**
	 * Gets a like object by type and object id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @return	LikeObject
	 */
	public static function getLikeObject($objectTypeID, $objectID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_like_object
			WHERE	objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeID,
			$objectID
		));
		$row = $statement->fetchArray();
		
		if (!$row) {
			$row = array();
		}
		
		return new LikeObject(null, $row);
	}
}
