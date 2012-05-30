<?php
namespace wcf\system\session;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * SessionHandler provides an abstract implementation for session handling.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class SessionHandler extends SingletonFactory {
	/**
	 * prevents update on shutdown
	 * @var	boolean
	 */	
	protected $doNotUpdate = false;
	
	/**
	 * various environment variables
	 * @var	array
	 */	
	protected $environment = array();
	
	/**
	 * group data and permissions
	 * @var array<array>
	 */	
	protected $groupData = null;
	
	/**
	 * language id for active user
	 * @var	integer
	 */	
	protected $languageID = 0;
	
	/**
	 * language ids for active user
	 * @var	array<integer>
	 */	
	protected $languageIDs = null;
	
	/**
	 * session object
	 * @var	wcf\data\acp\session\ACPSession
	 */	
	protected $session = null;
	
	/**
	 * session class name
	 * @var	string
	 */	
	protected $sessionClassName = '';
	
	/**
	 * session editor class name
	 * @var	string
	 */
	protected $sessionEditorClassName = '';
	
	/**
	 * enable cookie support
	 * @var	boolean
	 */
	protected $useCookies = false;
	
	/**
	 * user object
	 * @var	wcf\data\user\User
	 */	
	protected $user = null;
	
	/**
	 * session variables
	 * @var	array
	 */	
	protected $variables = null;
	
	/**
	 * indicates if session variables changed and must be saved upon shutdown
	 * @var	boolean
	 */	
	protected $variablesChanged = false;
	
	/**
	 * Provides access to session data.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */	
	public function __get($key) {
		if (isset($this->environment[$key])) {
			return $this->environment[$key];
		}
		
		return $this->session->{$key};
	}
	
	/**
	 * Loads an existing session or creates a new one.
	 *
	 * @param	string		$sessionEditorClassName
	 * @param	string		$sessionID
	 */	
	public function load($sessionEditorClassName, $sessionID) {
		$this->sessionEditorClassName = $sessionEditorClassName;
		$this->sessionClassName = call_user_func(array($sessionEditorClassName, 'getBaseClass'));
		
		// try to get existing session
		if (!empty($sessionID)) {
			$this->getExistingSession($sessionID);
		}
		
		// create new session
		if ($this->session === null) {
			$this->create();
		}
	}
	
	/**
	 * Initializes session system.
	 */	
	public function initSession() {
		// init session environment
		$this->loadVariables();
		$this->initSecurityToken();
		$this->defineConstants();
		
		// assign language id
		$this->languageID = $this->user->languageID;
		
		// init environment variables
		$this->initEnvironment();
	}
	
	/**
	 * Enables cookie support.
	 */	
	public function enableCookies() {
		$this->useCookies = true;
	}
	
	/**
	 * Initializes environment variables.
	 */
	protected function initEnvironment() {
		$this->environment = array(
			'lastRequestURI' => $this->session->requestURI,
			'lastRequestMethod' => $this->session->requestMethod,
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' => UserUtil::getUserAgent(),
			'requestURI' => UserUtil::getRequestURI(),
			'requestMethod' => (!empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '')
		);
	}
	
	/**
	 * Disables update on shutdown.
	 */
	public function disableUpdate() {
		$this->doNotUpdate = true;
	}
	
	/**
	 * Defines WCF-global constants related to session.
	 */
	protected function defineConstants() {
		if ($this->useCookies) {
			if (!defined('SID_ARG_1ST')) define('SID_ARG_1ST', '');
			if (!defined('SID_ARG_2ND')) define('SID_ARG_2ND', '');
			if (!defined('SID_ARG_2ND_NOT_ENCODED')) define('SID_ARG_2ND_NOT_ENCODED', '');
			if (!defined('SID')) define('SID', '');
			if (!defined('SID_INPUT_TAG')) define('SID_INPUT_TAG', '');
		}
		else {
			if (!defined('SID_ARG_1ST')) define('SID_ARG_1ST', '?s='.$this->sessionID);
			if (!defined('SID_ARG_2ND')) define('SID_ARG_2ND', '&amp;s='.$this->sessionID);
			if (!defined('SID_ARG_2ND_NOT_ENCODED')) define('SID_ARG_2ND_NOT_ENCODED', '&s='.$this->sessionID);
			if (!defined('SID')) define('SID', $this->sessionID);
			if (!defined('SID_INPUT_TAG')) define('SID_INPUT_TAG', '<input type="hidden" name="s" value="'.$this->sessionID.'" />');
		}
		
		// security token
		if (!defined('SECURITY_TOKEN')) define('SECURITY_TOKEN', $this->getSecurityToken());
		if (!defined('SECURITY_TOKEN_INPUT_TAG')) define('SECURITY_TOKEN_INPUT_TAG', '<input type="hidden" name="t" value="'.$this->getSecurityToken().'" />');
	}
	
	/**
	 * Initializes security token.
	 */	
	protected function initSecurityToken() {
		if ($this->getVar('__SECURITY_TOKEN') === null) {
			$this->register('__SECURITY_TOKEN', StringUtil::getRandomID());
		}
	}
	
	/**
	 * Returns security token.
	 * 
	 * @return	string
	 */
	public function getSecurityToken() {
		return $this->getVar('__SECURITY_TOKEN');
	}
	
	/**
	 * Validates the given security token, returns false if
	 * given token is invalid.
	 * 
	 * @param	string		$token
	 * @return	boolean
	 */
	public function checkSecurityToken($token) {
		return ($this->getSecurityToken() === $token);
	}
	
	/**
	 * Registers a session variable.
	 *
	 * @param	string		$key
	 * @param	string		$value
	 */	
	public function register($key, $value) {
		$this->variables[$key] = $value;
		$this->variablesChanged = true;
	}
	
	/**
	 * Unsets a session variable.
	 * 
	 * @param	string		$key
	 */	
	public function unregister($key) {
		unset($this->variables[$key]);
		$this->variablesChanged = true;
	}
	
	/**
	 * Returns the value of a session variable.
	 * 
	 * @param	string		$key
	 */
	public function getVar($key) {
		if (isset($this->variables[$key])) {
			return $this->variables[$key];
		}
		
		return null;
	}
	
	/**
	 * Initializes session variables.
	 */	
	protected function loadVariables() {
		@$this->variables = unserialize($this->session->sessionVariables);
		if (!is_array($this->variables)) {
			$this->variables = array();
		}
	}
	
	/**
	 * Returns the user object of this session.
	 *
	 * @return	User 	$user
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Tries to read existing session identified by $sessionID.
	 *
	 * @param	string		$sessionID 
	 * @return	UserSession
	 */	
	protected function getExistingSession($sessionID) {
		$this->session = new $this->sessionClassName($sessionID);
		if (!$this->session->sessionID || !$this->validate()) {
			$this->session = null;
			return;
		}
		
		// load user
		$this->user = new User($this->session->userID);
	}
	
	/**
	 * Validates the ip address and the user agent of this session.
	 * 
	 * @return	boolean
	 */
	protected function validate() {
		if (SESSION_VALIDATE_IP_ADDRESS) {
			if ($this->session->ipAddress != UserUtil::getIpAddress()) {
				return false;
			}
		}
		if (SESSION_VALIDATE_USER_AGENT) {
			if ($this->session->userAgent != UserUtil::getUserAgent()) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Creates a new session.
	 */	
	protected function create() {
		// create new session hash
		$sessionID = StringUtil::getRandomID();
		
		// get user automatically
		$this->user = UserAuthenticationFactory::getUserAuthentication()->loginAutomatically(call_user_func(array($this->sessionClassName, 'supportsPersistentLogins')));
		
		// create user
		if ($this->user === null) {
			// no valid user found
			// create guest user
			$this->user = new User(null);
		}
		
		if ($this->user->userID != 0) {
			// user is no guest
			// delete all other sessions of this user
			call_user_func(array($this->sessionEditorClassName, 'deleteUserSessions', array($this->user->userID)));
		}
		
		// save session
		$this->session = call_user_func(array($this->sessionEditorClassName, 'create'), array(
			'sessionID' => $sessionID,
			'packageID' => PACKAGE_ID,
			'userID' => $this->user->userID,
			'username' => $this->user->username,
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' => UserUtil::getUserAgent(),
			'lastActivityTime' => TIME_NOW,
			'requestURI' => UserUtil::getRequestURI(),
			'requestMethod' => (!empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '')
		));
	}
	
	/**
	 * Returns the value of the permission with the given name.
	 *
	 * @param 	string		$permission
	 * @return	mixed		permission value
	 */
	public function getPermission($permission) {
		$this->loadGroupData();
		
		if (!isset($this->groupData[$permission])) return false;
		return $this->groupData[$permission];
	}
	
	/**
	 * Checks if the active user has the given permissions and throws a 
	 * PermissionDeniedException if that isn't the case.
	 */
	public function checkPermissions(array $permissions) {
		foreach ($permissions as $permission) {
			if (!$this->getPermission($permission)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Loads group data from cache.
	 */	
	protected function loadGroupData() {
		if ($this->groupData !== null) return;
		
		// work-around for setup process (package wcf does not exist yet)
		if (!PACKAGE_ID) {
			$groupIDs = array();
			$sql = "SELECT	groupID
				FROM	wcf".WCF_N."_user_to_group
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->user->userID));
			while ($row = $statement->fetchArray()) {
				$groupIDs[] = $row['groupID'];
			}
		}
		else {
			$groupIDs = $this->user->getGroupIDs();
		}
		
		$groups = implode(',', $groupIDs);
		$groupsFileName = StringUtil::getHash($groups);
		
		// register cache resource
		$cacheName = 'groups-'.PACKAGE_ID.'-'.$groups;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.groups-'.PACKAGE_ID.'-'.$groupsFileName.'.php',
			'wcf\system\cache\builder\UserGroupPermissionCacheBuilder'
		);
		
		// get group data from cache
		$this->groupData = CacheHandler::getInstance()->get($cacheName);
		if (isset($this->groupData['groupIDs']) && $this->groupData['groupIDs'] != $groups) {
			$this->groupData = array();
		}
	}
	
	/**
	 * Returns language ids for active user.
	 *
	 * @return	array<integer>
	 */	
	public function getLanguageIDs() {
		$this->loadLanguageIDs();
		
		return $this->languageIDs;
	}
	
	/**
	 * Loads language ids for active user.
	 */	
	protected function loadLanguageIDs() {
		if ($this->languageIDs !== null) return;
		
		$this->languageIDs = array();
		
		if (!$this->user->userID) {
			return;
		}
		
		// work-around for setup process (package wcf does not exist yet)
		if (!PACKAGE_ID) {
			$sql = "SELECT	languageID
				FROM	wcf".WCF_N."_user_to_language
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->user->userID));
			while ($row = $statement->fetchArray()) {
				$this->languageIDs[] = $row['languageID'];
			}
		}
		else {
			$this->languageIDs = $this->user->getLanguageIDs();
		}
	}
	
	/**
	 * Stores a new user object in this session, e.g. a user was guest because not
	 * logged in, after the login his old session is used to store his full data.
	 *
	 * @param	User		$user
	 */	
	public function changeUser(User $user) {
		$sessionTable = call_user_func(array($this->sessionClassName, 'getDatabaseTableName'));
		
		if ($user->userID) {
			// user is not a guest, delete all other sessions of this user
			$sql = "SELECT		sessionID
				FROM		".$sessionTable."
				WHERE		sessionID <> ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->sessionID, $this->userID));
			$row = $statement->fetchArray();
			
			if ($row) {
				$sql = "DELETE FROM 	".$sessionTable."
					WHERE 		sessionID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$row['sessionID']
				));
			}
		}
		
		// update user reference
		$this->user = $user;
		
		// update session
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->update(array(
			'userID' => $this->user->userID,
			'username' => $this->user->username
		));
		
		// truncate session variables
	}
	
	/**
	 * Updates user session on shutdown.
	 */	
	public function update() {
		if ($this->doNotUpdate) return;
		
		// set up data
		$data = array(
			'ipAddress' => $this->ipAddress,
			'userAgent' => $this->userAgent,
			'requestURI' => $this->requestURI,
			'requestMethod' => $this->requestMethod,
			'lastActivityTime' => TIME_NOW,
			'packageID' => PACKAGE_ID
		);
		if ($this->variablesChanged) {
			$data['sessionVariables'] = serialize($this->variables);
		}
		
		// update session
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->update($data);
	}
	
	/**
	 * Deletes this session and it's related data.
	 */	
	public function delete() {
		// remove session
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->delete();
		
		// clear storage
		if ($this->user->userID) {
			self::resetSessions(array($this->user->userID));
		}
		
		// disable update
		$this->disableUpdate();
	}
	
	/**
	 * Returns currently active language id
	 *
	 * @return	integer
	 */	
	public function getLanguageID() {
		return $this->languageID;
	}
	
	/**
	 * Resets session-specific storage data.
	 *
	 * @param	array<integer>	$userIDs
	 */	
	public static function resetSessions(array $userIDs = array()) {
		if (count($userIDs)) {
			UserStorageHandler::getInstance()->reset($userIDs, 'groupIDs', 1);
			UserStorageHandler::getInstance()->reset($userIDs, 'languageIDs', 1);
		}
		else {
			UserStorageHandler::getInstance()->resetAll('groupIDs', 1);
			UserStorageHandler::getInstance()->resetAll('languageIDs', 1);
		}
	}
}
