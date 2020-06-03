<?php
namespace wcf\data\user;
use wcf\data\language\Language;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObject;
use wcf\data\IUserContent;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\PasswordUtil;
use wcf\util\UserUtil;

/**
 * Represents a user.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 * 
 * @property-read	integer		$userID				unique id of the user
 * @property-read	string		$username			name of the user
 * @property-read	string		$email				email address of the user
 * @property-read	string		$password			double salted hash of the user's password
 * @property-read	string		$accessToken			token used for access authentication, for example used by feed pages
 * @property-read	integer		$languageID			id of the interface language used by the user
 * @property-read	integer		$registrationDate		timestamp at which the user has registered/has been created
 * @property-read	integer		$styleID			id of the style used by the user
 * @property-read	integer		$banned				is `1` if the user is banned, otherwise `0`
 * @property-read	string		$banReason			reason why the user is banned
 * @property-read	integer		$banExpires			timestamp at which the banned user is automatically unbanned
 * @property-read	integer		$activationCode			code sent to the user's email address used for account activation
 * @property-read	integer		$lastLostPasswordRequestTime	timestamp at which the user has reported that they lost their password or 0 if password has not been reported as lost
 * @property-read	string		$lostPasswordKey		code used for authenticating setting new password after password loss or empty if password has not been reported as lost
 * @property-read	integer		$lastUsernameChange		timestamp at which the user changed their name the last time or 0 if username has not been changed
 * @property-read	string		$newEmail			new email address of the user that has to be manually confirmed or empty if no new email address has been set
 * @property-read	string		$oldUsername			previous name of the user or empty if they have had no previous name
 * @property-read	integer		$quitStarted			timestamp at which the user terminated their account
 * @property-read	integer		$reactivationCode		code used for authenticating setting new email address or empty if no new email address has been set
 * @property-read	string		$registrationIpAddress		ip address of the user at the time of registration or empty if user has been created manually or if no ip address are logged
 * @property-read	integer|null	$avatarID			id of the user's avatar or null if they have no avatar
 * @property-read	integer		$disableAvatar			is `1` if the user's avatar has been disabled, otherwise `0`
 * @property-read	string		$disableAvatarReason		reason why the user's avatar is disabled
 * @property-read	integer		$disableAvatarExpires		timestamp at which the user's avatar will automatically be enabled again
 * @property-read	integer		$enableGravatar			is `1` if the user uses a gravatar as avatar, otherwise `0`
 * @property-read	string		$gravatarFileExtension		extension of the user's gravatar file
 * @property-read	string		$signature			text of the user's signature
 * @property-read	integer		$signatureEnableHtml		is `1` if HTML will rendered in the user's signature, otherwise `0`
 * @property-read	integer		$disableSignature		is `1` if the user's signature has been disabled, otherwise `0`
 * @property-read	string		$disableSignatureReason		reason why the user's signature is disabled
 * @property-read	integer		$disableSignatureExpires	timestamp at which the user's signature will automatically be enabled again
 * @property-read	integer		$lastActivityTime		timestamp of the user's last activity
 * @property-read	integer		$profileHits			number of times the user's profile has been visited
 * @property-read	integer|null	$rankID				id of the user's rank or null if they have no rank
 * @property-read	string		$userTitle			custom user title used instead of rank title or empty if user has no custom title
 * @property-read	integer|null	$userOnlineGroupID		id of the user group whose online marking is used when printing the user's formatted name or null if no special marking is used
 * @property-read	integer		$activityPoints			total number of the user's activity points
 * @property-read	string		$notificationMailToken		token used for authenticating requests by the user to disable notification emails
 * @property-read	string		$authData			data of the third party used for authentication
 * @property-read	integer		$likesReceived			cumulative result of likes (counting +1) the user's contents have received
 * @property-read       string          $coverPhotoHash                 hash of the user's cover photo
 * @property-read	string		$coverPhotoExtension		extension of the user's cover photo file
 * @property-read       integer         $disableCoverPhoto              is `1` if the user's cover photo has been disabled, otherwise `0`
 * @property-read	string		$disableCoverPhotoReason	reason why the user's cover photo is disabled
 * @property-read	integer		$disableCoverPhotoExpires	timestamp at which the user's cover photo will automatically be enabled again
 */
final class User extends DatabaseObject implements IRouteController, IUserContent {
	/**
	 * list of group ids
	 * @var integer[]
	 */
	protected $groupIDs;
	
	/**
	 * true, if user has access to the ACP
	 * @var	boolean
	 */
	protected $hasAdministrativePermissions;
	
	/**
	 * list of language ids
	 * @var	integer[]
	 */
	protected $languageIDs;
	
	/**
	 * date time zone object
	 * @var	\DateTimeZone
	 */
	protected $timezoneObj;
	
	/**
	 * list of user options
	 * @var	UserOption[]
	 */
	protected static $userOptions;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * @inheritDoc
	 */
	public function __construct($id, $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT		user_option_value.*, user_table.*
				FROM		wcf".WCF_N."_user user_table
				LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
				ON		(user_option_value.userID = user_table.userID)
				WHERE		user_table.userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$id]);
			$row = $statement->fetchArray();
			
			// enforce data type 'array'
			if ($row === false) $row = [];
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
			if (CryptoUtil::secureCompare($this->password, PasswordUtil::getDoubleSaltedHash($password, $this->password))) {
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
			$userEditor->update([
				'password' => $password
			]);
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
		if (PasswordUtil::isBlowfish($this->password) && CryptoUtil::secureCompare($this->password, PasswordUtil::getSaltedHash($passwordHash, $this->password))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns an array with all the groups in which the actual user is a member.
	 * 
	 * @param	boolean		$skipCache
	 * @return	integer[]
	 */
	public function getGroupIDs($skipCache = false) {
		if ($this->groupIDs === null || $skipCache) {
			if (!$this->userID) {
				// user is a guest, use default guest group
				$this->groupIDs = UserGroup::getGroupIDsByType([UserGroup::GUESTS, UserGroup::EVERYONE]);
			}
			else {
				// get group ids
				$data = UserStorageHandler::getInstance()->getField('groupIDs', $this->userID);
				
				// cache does not exist or is outdated
				if ($data === null || $skipCache) {
					$sql = "SELECT	groupID
						FROM	wcf".WCF_N."_user_to_group
						WHERE	userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$this->userID]);
					$this->groupIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
					
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
	 * @return	integer[]
	 */
	public function getLanguageIDs() {
		if ($this->languageIDs === null) {
			$this->languageIDs = [];
			
			if ($this->userID) {
				// get language ids
				$data = UserStorageHandler::getInstance()->getField('languageIDs', $this->userID);
				
				// cache does not exist or is outdated
				if ($data === null) {
					$sql = "SELECT	languageID
						FROM	wcf".WCF_N."_user_to_language
						WHERE	userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$this->userID]);
					$this->languageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
					
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
	 * @param       boolean         $filterDisabled suppress values for disabled options
	 * @return	mixed				user option value
	 */
	public function getUserOption($name, $filterDisabled = false) {
		$optionID = self::getUserOptionID($name);
		if ($optionID === null) {
			return null;
		}
		else if ($filterDisabled && self::$userOptions[$name]->isDisabled) {
			return null;
		}
		
		if (!isset($this->data['userOption'.$optionID])) return null;
		return $this->data['userOption'.$optionID];
	}
	
	/**
	 * Fetches all user options from cache.
	 */
	protected static function getUserOptionCache() {
		self::$userOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
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
	 * @inheritDoc
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
	 * @return	User
	 */
	public static function getUserByUsername($username) {
		$sql = "SELECT		user_option_value.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = user_table.userID)
			WHERE		user_table.username = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$username]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new User(null, $row);
	}
	
	/**
	 * Returns the user with the given email.
	 * 
	 * @param	string		$email
	 * @return	User
	 */
	public static function getUserByEmail($email) {
		$sql = "SELECT		user_option_value.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = user_table.userID)
			WHERE		user_table.email = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$email]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new User(null, $row);
	}

	/**
	 * Returns the user with the given authData.
	 *
	 * @param	string		$authData
	 * @return	User
	 */
	public static function getUserByAuthData($authData) {
		$sql = "SELECT		user_option_value.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = user_table.userID)
			WHERE		user_table.authData = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$authData]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];

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
	 * @return	User[]
	 */
	public static function getUsers(array $userIDs) {
		$userList = new UserList();
		$userList->setObjectIDs($userIDs);
		$userList->readObjects();
		
		return $userList->getObjects();
	}
	
	/**
	 * Returns username.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return ($this->username ?: '');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		return 'user_table';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->username;
	}
	
	/**
	 * Returns the language of this user.
	 * 
	 * @return	Language
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
	 * @inheritDoc
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return $this->registrationDate;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('User', [
			'application' => 'wcf',
			'object' => $this,
			'forceFrontend' => true
		]);
	}
	
	/**
	 * Returns the social network privacy settings of the user.
	 * @deprecated 3.0
	 * 
	 * @return	boolean[]
	 */
	public function getSocialNetworkPrivacySettings() {
		return [
			'facebook' => false,
			'google' => false,
			'reddit' => false,
			'twitter' => false
		];
	}
	
	/**
	 * Returns the registration ip address, attempts to convert to IPv4.
	 * 
	 * @return      string
	 */
	public function getRegistrationIpAddress() {
		if ($this->registrationIpAddress) {
			return UserUtil::convertIPv6To4($this->registrationIpAddress);
		}
		
		return '';
	}
}
