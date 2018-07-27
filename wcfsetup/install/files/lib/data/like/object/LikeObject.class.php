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
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like\Object
 *
 * @property-read	integer		$likeObjectID		unique id of the liked object
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.like.likeableObject` object type
 * @property-read	integer		$objectID		id of the liked object
 * @property-read	integer|null	$objectUserID		id of the user who created the liked object or null if user has been deleted or object was created by guest
 * @property-read	integer		$likes			number of likes of the liked object
 * @property-read	integer		$dislikes		number of dislikes of the liked object
 * @property-read	integer		$cumulativeLikes	cumulative result of likes (counting +1) and dislikes (counting -1)
 * @property-read	string		$cachedUsers		serialized array with the ids and names of the three users who liked (+1) the object last
 */
class LikeObject extends DatabaseObject {
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
	protected $users = [];
	
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
	 * Returns the first 3 users who liked this object.
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
	 * Returns the like object with the given type and object id.
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
		$statement->execute([
			$objectTypeID,
			$objectID
		]);
		$row = $statement->fetchArray();
		
		if (!$row) {
			$row = [];
		}
		
		return new LikeObject(null, $row);
	}
}
