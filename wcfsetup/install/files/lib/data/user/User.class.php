<?php
namespace wcf\data\user;
use wcf\data\DatabaseObject;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\system\cache\CacheHandler;
use wcf\system\storage\StorageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category 	Community Framework
 */
class User extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'userID';
	
	/**
	 * list of group ids
	 *
	 * @var	array<integer>
	 */
	protected $groupIDs = null;
	
	/**
	 * list of language ids
	 * 
	 * @var	array<integer>
	 */
	protected $languageIDs = null;
	
	/**
	 * date time zone object
	 * @var DateTimeZone
	 */
	protected $timezoneObj = null;
	
	/**
	 * list of user options.
	 *
	 * @var	array<string>
	 */
	protected static $userOptions = null;
	
	/**
	 * Returns true, if the given password is the correct password for this user.
	 *
	 * @param 	string		$password
	 * @return 	boolean 	password correct
	 */
	public function checkPassword($password) {
		return ($this->password == StringUtil::getDoubleSaltedHash($password, $this->salt));
	}
	
	/**
	 * Returns true, if the given password hash from a cookie is the correct password for this user.
	 *
	 * @param 	string		$passwordHash
	 * @return 	boolean 	password correct
	 */
	public function checkCookiePassword($passwordHash) {
		return ($this->password == StringUtil::encrypt($this->salt . $passwordHash));
	}
	
	/**
	 * Returns an array with the all the groups in which the actual user is a member.
	 *
	 * @return 	array 		$groupIDs
	 */
	public function getGroupIDs() {
		if ($this->groupIDs === null) {
			if (!$this->userID) {
				// user is a guest, use default guest group
				$this->groupIDs = UserGroup::getGroupIDsByType(array(UserGroup::GUESTS));
			}
			else {
				// load storage data
				StorageHandler::getInstance()->loadStorage(array($this->userID), 1);
				
				// get group ids
				$data = StorageHandler::getInstance()->getStorage(array($this->userID), 'groupIDs', 1);
				
				// cache does not exist or is outdated
				if ($data[$this->userID] === null) {
					$this->groupIDs = array();
					$sql = "SELECT	groupID
						FROM	wcf".WCF_N."_user_to_group
						WHERE	userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array($this->userID));
					while ($row = $statement->fetchArray()) {
						$this->groupIDs[] = $row['groupID'];
					}
					
					// update storage data
					StorageHandler::getInstance()->update($this->userID, 'groupIDs', serialize($this->groupIDs), 1);
				}
				else {
					$this->groupIDs = unserialize($data[$this->userID]);
				}
			}
		}
		
		return $this->groupIDs;
	}
	
	/**
	 * Returns a list of language ids for this user.
	 * 
	 * @return	array<integer>
	 */
	public function getLanguageIDs() {
		if ($this->languageIDs === null) {
			// load storage data
			StorageHandler::getInstance()->loadStorage(array($this->userID), 1);
			
			// get group ids
			$data = StorageHandler::getInstance()->getStorage(array($this->userID), 'languageIDs', 1);
			
			// cache does not exist or is outdated
			if ($data[$this->userID] === null) {
				$sql = "SELECT	languageID
					FROM	wcf".WCF_N."_user_to_language
					WHERE	userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array($this->userID));
				while ($row = $statement->fetchArray()) {
					$this->languageIDs[] = $row['languageID'];
				}
				
				// update storage data
				StorageHandler::getInstance()->update($this->userID, 'languageIDs', serialize($this->languageIDs), 1);
			}
			else {
				$this->languageIDs = unserialize($data[$this->userID]);
			}
		}
		
		return $this->languageIDs;
	}
	
	/**
	 * Returns the value of the user option with the given name.
	 *
	 * @param	string		$name		user option name
	 * @return	mixed				user option value
	 */
	public function getUserOption($name) {
		$optionID = self::getUserOptionID($name);
		if ($optionID === null) {
			return null;
		}
		
		if (!isset($this->data['userOption'.$optionID])) return null;
		return $this->data['userOption'.$optionID];
	}
	
	/**
	 * Gets all user options from cache.
	 */
	protected static function getUserOptionCache() {
		$cacheName = 'user-option-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', 'wcf\system\cache\builder\CacheBuilderOption');
		self::$userOptions = CacheHandler::getInstance()->get($cacheName, 'options');
	}
	
	/**
	 * Returns the id of a user option.
	 *
	 * @param	string		$name
	 * @return	integer		id
	 */
	public static function getUserOptionID($name) {
		// get user option cache if necessary
		if (self::$userOptions === null) {
			self::getUserOptionCache();
		}
		
		if (!isset(self::$userOptions[$name])) {
			return null;
		}
		
		return self::$userOptions[$name]['optionID'];
	}
	
	/**
	 * @see wcf\data\DatabaseObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		if ($value === null) $value = $this->getUserOption($name);
		return $value;
	}
	
	/**
	 * Returns User-object by username.
	 *
	 * @param	string		$username
	 * @return	User
	 */
	public static function getUserByUsername($username) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user
			WHERE	username = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($username));
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new User(null, $row);
	}
	
	/**
	 * Returns User-object by email.
	 *
	 * @param	string		$email
	 * @return	User
	 */
	public static function getUserByEmail($email) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user
			WHERE	email = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($email));
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new User(null, $row);
	}
	
	/**
	 * Returns true, if this user is marked.
	 *
	 * @return 	boolean
	 */
	public function isMarked() {
		$markedUsers = WCF::getSession()->getVar('markedUsers');
		if ($markedUsers !== null) {
			if (in_array($this->userID, $markedUsers)) return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns the time zone of this user.
	 *
	 * @return DateTimeZone
	 */
	public function getTimeZone() {
		if ($this->timezoneObj === null) {
			if ($this->timezone) {
				$this->timezoneObj = new \DateTimeZone($this->timezone);
			}
			else {
				$this->timezoneObj = new \DateTimeZone(TIMEZONE);
			}
		}
		
		return $this->timezoneObj;
	}
	
	/**
	 * Returns a list of users.
	 * 
	 * @param	array		$userIDs
	 * @return	array<User>
	 */	
	public static function getUsers(array $userIDs) {
		$userList = new UserList();
		$userList->getConditionBuilder()->add("user_table.userID IN (?)", array($userIDs));
		$userList->readObjects();
		
		return $userList->getObjects();
	}
}
