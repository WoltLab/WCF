<?php
namespace wcf\data\user;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\data\DatabaseObject;
use wcf\data\IUserContent;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\PasswordUtil;

/**
 * Represents a user.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
final class User extends DatabaseObject implements IRouteController, IUserContent {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'userID';
	
	/**
	 * list of group ids
	 * @var	array<integer>
	 */
	protected $groupIDs = null;
	
	/**
	 * true, if user has access to the ACP
	 * @var	boolean
	 */
	protected $hasAdministrativePermissions = null;
	
	/**
	 * list of language ids
	 * @var	array<integer>
	 */
	protected $languageIDs = null;
	
	/**
	 * date time zone object
	 * @var	DateTimeZone
	 */
	protected $timezoneObj = null;
	
	/**
	 * list of user options
	 * @var	array<string>
	 */
	protected static $userOptions = null;
	
	/**
	 * @see	\wcf\data\DatabaseObject::__construct()
	 */
	public function __construct($id, $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT		user_option_value.*, user_table.*
				FROM		wcf".WCF_N."_user user_table
				LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
				ON		(user_option_value.userID = user_table.userID)
				WHERE		user_table.userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($id));
			$row = $statement->fetchArray();
			
			// enforce data type 'array'
			if ($row === false) $row = array();
		}
		else if ($object !== null) {
			$row = $object->data;
		}
		
		$this->handleData($row);
	}
	
	/**
	 * Returns true if the given password is the correct password for this user.
	 * 
	 * @param	string		$password
	 * @return	boolean		password correct
	 */
	public function checkPassword($password) {
		$isValid = false;
		$rebuild = false;
		
		// check if password is a valid bcrypt hash
		if (PasswordUtil::isBlowfish($this->password)) {
			if (PasswordUtil::isDifferentBlowfish($this->password)) {
				$rebuild = true;
			}
			
			// password is correct
			if (PasswordUtil::secureCompare($this->password, PasswordUtil::getDoubleSaltedHash($password, $this->password))) {
				$isValid = true;
			}
		}
		else {
			// different encryption type
			if (PasswordUtil::checkPassword($this->username, $password, $this->password)) {
				$isValid = true;
				$rebuild = true;
			}
		}
		
		// create new password hash, either different encryption or different blowfish cost factor
		if ($rebuild && $isValid) {
			$userEditor = new UserEditor($this);
			$userEditor->update(array(
				'password' => $password
			));
		}
		
		return $isValid;
	}
	
	/**
	 * Returns true if the given password hash from a cookie is the correct password for this user.
	 * 
	 * @param	string		$passwordHash
	 * @return	boolean		password correct
	 */
	public function checkCookiePassword($passwordHash) {
		if (PasswordUtil::isBlowfish($this->password) && PasswordUtil::secureCompare($this->password, PasswordUtil::getSaltedHash($passwordHash, $this->password))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns an array with all the groups in which the actual user is a member.
	 * 
	 * @param	boolean		$skipCache
	 * @return	array		$groupIDs
	 */
	public function getGroupIDs($skipCache = false) {
		if ($this->groupIDs === null || $skipCache) {
			if (!$this->userID) {
				// user is a guest, use default guest group
				$this->groupIDs = UserGroup::getGroupIDsByType(array(UserGroup::GUESTS, UserGroup::EVERYONE));
			}
			else {
				// get group ids
				$data = UserStorageHandler::getInstance()->getField('groupIDs', $this->userID);
				
				// cache does not exist or is outdated
				if ($data === null || $skipCache) {
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
					if (!$skipCache) {
						UserStorageHandler::getInstance()->update($this->userID, 'groupIDs', serialize($this->groupIDs));
					}
				}
				else {
					$this->groupIDs = unserialize($data);
				}
			}
			
			sort($this->groupIDs, SORT_NUMERIC);
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
			$this->languageIDs = array();
			
			if ($this->userID) {
				// get language ids
				$data = UserStorageHandler::getInstance()->getField('languageIDs', $this->userID);
				
				// cache does not exist or is outdated
				if ($data === null) {
					$sql = "SELECT	languageID
						FROM	wcf".WCF_N."_user_to_language
						WHERE	userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array($this->userID));
					while ($row = $statement->fetchArray()) {
						$this->languageIDs[] = $row['languageID'];
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update($this->userID, 'languageIDs', serialize($this->languageIDs));
				}
				else {
					$this->languageIDs = unserialize($data);
				}
			}
			else if (!WCF::getSession()->spiderID) {
				$this->languageIDs[] = WCF::getLanguage()->languageID;
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
		self::$userOptions = UserOptionCacheBuilder::getInstance()->getData(array(), 'options');
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
		
		return self::$userOptions[$name]->optionID;
	}
	
	/**
	 * @see	\wcf\data\DatabaseObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		if ($value === null) $value = $this->getUserOption($name);
		return $value;
	}
	
	/**
	 * Returns the user with the given username.
	 * 
	 * @param	string		$username
	 * @return	\wcf\data\user\User
	 */
	public static function getUserByUsername($username) {
		$sql = "SELECT		user_option_value.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = user_table.userID)
			WHERE		user_table.username = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($username));
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new User(null, $row);
	}
	
	/**
	 * Returns the user with the given email.
	 * 
	 * @param	string		$email
	 * @return	\wcf\data\user\User
	 */
	public static function getUserByEmail($email) {
		$sql = "SELECT		user_option_value.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = user_table.userID)
			WHERE		user_table.email = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($email));
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new User(null, $row);
	}
	
	/**
	 * Returns true if this user is marked.
	 * 
	 * @return	boolean
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
	 * @return	\DateTimeZone
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
	 * @return	array<\wcf\data\user\User>
	 */
	public static function getUsers(array $userIDs) {
		$userList = new UserList();
		$userList->getConditionBuilder()->add("user_table.userID IN (?)", array($userIDs));
		$userList->readObjects();
		
		return $userList->getObjects();
	}
	
	/**
	 * Returns username.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->username;
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableAlias()
	 */
	public static function getDatabaseTableAlias() {
		return 'user_table';
	}
	
	/**
	 * @see	\wcf\system\request\IRouteController::getTitle()
	 */
	public function getTitle() {
		return $this->username;
	}
	
	/**
	 * Returns the language of this user.
	 * 
	 * @return	\wcf\data\language\Language
	 */
	public function getLanguage() {
		$language = LanguageFactory::getInstance()->getLanguage($this->languageID);
		if ($language === null) {
			$language = LanguageFactory::getInstance()->getLanguage(LanguageFactory::getInstance()->getDefaultLanguageID());
		}
		
		return $language;
	}
	
	/**
	 * Returns true if the active user can edit this user.
	 * 
	 * @return	boolean
	 */
	public function canEdit() {
		return (WCF::getSession()->getPermission('admin.user.canEditUser') && UserGroup::isAccessibleGroup($this->getGroupIDs()));
	}
	
	/**
	 * Returns true, if this user has access to the ACP.
	 * 
	 * @return	boolean
	 */
	public function hasAdministrativeAccess() {
		if ($this->hasAdministrativePermissions === null) {
			$this->hasAdministrativePermissions = false;
			
			if ($this->userID) {
				foreach ($this->getGroupIDs() as $groupID) {
					$group = UserGroup::getGroupByID($groupID);
					if ($group->isAdminGroup()) {
						$this->hasAdministrativePermissions = true;
						break;
					}
				}
			}
		}
		
		return $this->hasAdministrativePermissions;
	}
	
	/**
	 * @see	\wcf\data\IMessage::getUserID()
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @see	\wcf\data\IMessage::getUsername()
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * @see	\wcf\data\IMessage::getTime()
	 */
	public function getTime() {
		return $this->registrationDate;
	}
	
	/**
	 * @see	\wcf\data\ILinkableObject::getLink()
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('User', array(
			'application' => 'wcf',
			'object' => $this,
			'forceFrontend' => true
		));
	}
	
	public function getSocialNetworkPrivacySettings() {
		$settings = false;
		if ($this->userID && WCF::getUser()->socialNetworkPrivacySettings) {
			$settings = @unserialize(WCF::getUser()->socialNetworkPrivacySettings);
		}
		
		if ($settings === false) {
			$settings = array(
				'facebook' => false,
				'google' => false,
				'reddit' => false,
				'twitter' => false
			);
		}
		
		return $settings;
	}
}
