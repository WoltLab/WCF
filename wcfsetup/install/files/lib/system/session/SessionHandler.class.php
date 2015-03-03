<?php
namespace wcf\system\session;
use wcf\data\session\virtual\SessionVirtual;
use wcf\data\session\virtual\SessionVirtualAction;
use wcf\data\session\virtual\SessionVirtualEditor;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\page\ITrackablePage;
use wcf\system\cache\builder\SpiderCacheBuilder;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\cache\builder\UserGroupPermissionCacheBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\RequestHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Handles sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category	Community Framework
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
	 * @var	array<array>
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
	 * @var	\wcf\data\acp\session\ACPSession
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
	 * virtual session support
	 * @var	boolean
	 */
	protected $supportsVirtualSessions = false;
	
	/**
	 * style id
	 * @var	integer
	 */
	protected $styleID = null;
	
	/**
	 * enable cookie support
	 * @var	boolean
	 */
	protected $useCookies = false;
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
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
	 * virtual session object, null for guests
	 * @var	\wcf\data\session\virtual\SessionVirtual
	 */
	protected $virtualSession = false;
	
	/**
	 * true if this is a new session
	 * @var	boolean
	 */
	protected $firstVisit = false;
	
	/**
	 * list of names of permissions only available for users
	 * @var	array<string>
	 */
	protected $usersOnlyPermissions = array();
	
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
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->usersOnlyPermissions = UserGroupOptionCacheBuilder::getInstance()->getData(array(), 'usersOnlyOptions');
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
		$this->supportsVirtualSessions = call_user_func(array($this->sessionClassName, 'supportsVirtualSessions'));
		
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
		
		// session id change was delayed to the next request
		// as the SID constants already were defined
		if ($this->getVar('__changeSessionID')) {
			$this->unregister('__changeSessionID');
			$this->changeSessionID();
		}
		$this->defineConstants();
		
		// assign language and style id
		$this->languageID = ($this->getVar('languageID') === null) ? $this->user->languageID : $this->getVar('languageID');
		$this->styleID = ($this->getVar('styleID') === null) ? $this->user->styleID : $this->getVar('styleID');
		
		// init environment variables
		$this->initEnvironment();
	}
	
	/**
	 * Changes the session id to a new random one.
	 * 
	 * Usually a change is requested after login to ensure
	 * that the user is not running a fixated session by an
	 * attacker.
	 */
	protected function changeSessionID() {
		$oldSessionID = $this->session->sessionID;
		$newSessionID = StringUtil::getRandomID();
		
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->update(array(
			'sessionID' => $newSessionID
		));
		
		// fetch new session data from database
		$this->session = new $this->sessionClassName($newSessionID);
		
		if ($this->useCookies) {
			// we know that the user accepts cookies, simply send new session id
			HeaderUtil::setCookie('cookieHash', $newSessionID);
		}
		else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// user maybe does not accept cookies, replace session id in url
			// otherwise reloading the page will generate a new session
			
			$this->update();
			HeaderUtil::redirect(str_replace('s='.$oldSessionID, 's='.$newSessionID, UserUtil::getRequestURI()));
			exit;
		}
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
			'requestMethod' => (!empty($_SERVER['REQUEST_METHOD']) ? substr($_SERVER['REQUEST_METHOD'], 0, 7) : '')
		);
	}
	
	/**
	 * Disables update on shutdown.
	 */
	public function disableUpdate() {
		$this->doNotUpdate = true;
	}
	
	/**
	 * Defines global wcf constants related to session.
	 */
	protected function defineConstants() {
		if ($this->useCookies || $this->session->spiderID) {
			if (!defined('SID_ARG_1ST')) define('SID_ARG_1ST', '');
			if (!defined('SID_ARG_2ND')) define('SID_ARG_2ND', '');
			if (!defined('SID_ARG_2ND_NOT_ENCODED')) define('SID_ARG_2ND_NOT_ENCODED', '');
			if (!defined('SID')) define('SID', '');
			if (!defined('SID_INPUT_TAG')) define('SID_INPUT_TAG', '');
		}
		else {
			if (!defined('SID_ARG_1ST')) define('SID_ARG_1ST', '?s='.$this->session->sessionID);
			if (!defined('SID_ARG_2ND')) define('SID_ARG_2ND', '&amp;s='.$this->session->sessionID);
			if (!defined('SID_ARG_2ND_NOT_ENCODED')) define('SID_ARG_2ND_NOT_ENCODED', '&s='.$this->session->sessionID);
			if (!defined('SID')) define('SID', $this->session->sessionID);
			if (!defined('SID_INPUT_TAG')) define('SID_INPUT_TAG', '<input type="hidden" name="s" value="'.$this->session->sessionID.'" />');
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
		return PasswordUtil::secureCompare($this->getSecurityToken(), $token);
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
	 * @return	\wcf\data\user\User	$user
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Tries to read existing session identified by the given session id.
	 * 
	 * @param	string		$sessionID
	 */
	protected function getExistingSession($sessionID) {
		$this->session = new $this->sessionClassName($sessionID);
		if (!$this->session->sessionID) {
			$this->session = null;
			return;
		}
		
		$this->user = new User($this->session->userID);
		if ($this->supportsVirtualSessions) $this->virtualSession = SessionVirtual::getExistingSession($sessionID);
		
		if (!$this->validate()) {
			$this->session = null;
			$this->user = null;
			$this->virtualSession = false;
			
			return;
		}
		
		if ($this->supportsVirtualSessions) $this->loadVirtualSession();
	}
	
	/**
	 * Loads the virtual session object unless the user is not logged in or the session
	 * does not support virtual sessions. If there is no virtual session yet, it will be
	 * created on-the-fly.
	 * 
	 * @param	boolean		$forceReload
	 */
	protected function loadVirtualSession($forceReload = false) {
		if ($this->virtualSession === false || $forceReload) {
			$this->virtualSession = null;
			if ($this->user->userID && $this->supportsVirtualSessions) {
				$virtualSessionAction = new SessionVirtualAction(array(), 'create', array('data' => array('sessionID' => $this->session->sessionID)));
				
				try {
					$returnValues = $virtualSessionAction->executeAction();
					$this->virtualSession = $returnValues['returnValues'];
				}
				catch (DatabaseException $e) {
					// MySQL error 23000 = unique key
					// do not check against the message itself, some weird systems localize them
					if ($e->getCode() == 23000) {
						$this->virtualSession = SessionVirtual::getExistingSession($this->session->sessionID);
					}
				}
			}
		}
	}
	
	/**
	 * Validates the ip address and the user agent of this session.
	 * 
	 * @return	boolean
	 */
	protected function validate() {
		if (SESSION_VALIDATE_IP_ADDRESS) {
			if ($this->supportsVirtualSessions && ($this->virtualSession instanceof SessionVirtual)) {
				if ($this->virtualSession->ipAddress != UserUtil::getIpAddress()) {
					return false;
				}
			}
			else if ($this->session->ipAddress != UserUtil::getIpAddress()) {
				return false;
			}
		}
		
		if (SESSION_VALIDATE_USER_AGENT) {
			if ($this->supportsVirtualSessions && ($this->virtualSession instanceof SessionVirtual)) {
				if ($this->virtualSession->userAgent != UserUtil::getUserAgent()) {
					return false;
				}
			}
			else if ($this->session->userAgent != UserUtil::getUserAgent()) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Creates a new session.
	 */
	protected function create() {
		$spiderID = null;
		if ($this->sessionEditorClassName == 'wcf\data\session\SessionEditor') {
			// get spider information
			$spiderID = $this->getSpiderID(UserUtil::getUserAgent());
			if ($spiderID !== null) {
				// try to use existing session
				if (($session = $this->getExistingSpiderSession($spiderID)) !== null) {
					$this->user = new User(null);
					$this->session = $session;
					return;
				}
			}
		}
		
		// create new session hash
		$sessionID = StringUtil::getRandomID();
		
		// get user automatically
		$this->user = UserAuthenticationFactory::getInstance()->getUserAuthentication()->loginAutomatically(call_user_func(array($this->sessionClassName, 'supportsPersistentLogins')));
		
		// create user
		if ($this->user === null) {
			// no valid user found
			// create guest user
			$this->user = new User(null);
		}
		else if (!$this->supportsVirtualSessions) {
			// delete all other sessions of this user
			call_user_func(array($this->sessionEditorClassName, 'deleteUserSessions'), array($this->user->userID));
		}
		
		$createNewSession = true;
		if ($this->supportsVirtualSessions) {
			// find existing session
			$session = call_user_func(array($this->sessionClassName, 'getSessionByUserID'), $this->user->userID);
			
			if ($session !== null) {
				// inherit existing session
				$this->session = $session;
				$this->loadVirtualSession(true);
					
				$createNewSession = false;
			}
		}
		
		if ($createNewSession) {
			// save session
			$sessionData = array(
				'sessionID' => $sessionID,
				'userID' => $this->user->userID,
				'ipAddress' => UserUtil::getIpAddress(),
				'userAgent' => UserUtil::getUserAgent(),
				'lastActivityTime' => TIME_NOW,
				'requestURI' => UserUtil::getRequestURI(),
				'requestMethod' => (!empty($_SERVER['REQUEST_METHOD']) ? substr($_SERVER['REQUEST_METHOD'], 0, 7) : '')
			);
			
			if ($spiderID !== null) $sessionData['spiderID'] = $spiderID;
			
			try {
				$this->session = call_user_func(array($this->sessionEditorClassName, 'create'), $sessionData);
			}
			catch (DatabaseException $e) {
				// MySQL error 23000 = unique key
				// do not check against the message itself, some weird systems localize them
				if ($e->getCode() == 23000 && $this->supportsVirtualSessions) {
					// find existing session
					$session = call_user_func(array($this->sessionClassName, 'getSessionByUserID'), $this->user->userID);
					
					if ($session === null) {
						// MySQL reported a unique key error, but no corresponding session exists, rethrow exception
						throw $e;
					}
					else {
						// inherit existing session
						$this->session = $session;
						$this->loadVirtualSession(true);
					}
				}
				else {
					// unrelated to user id
					throw $e;
				}
			}
			
			$this->firstVisit = true;
			$this->loadVirtualSession(true);
		}
	}
	
	/**
	 * Returns the value of the permission with the given name.
	 * 
	 * @param	string		$permission
	 * @return	mixed		permission value
	 */
	public function getPermission($permission) {
		// check if a users only permission is checked for a guest and return
		// false if that is the case
		if (!$this->user->userID && in_array($permission, $this->usersOnlyPermissions)) {
			return false;
		}
		
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
		
		// get group data from cache
		$this->groupData = UserGroupPermissionCacheBuilder::getInstance()->getData($groupIDs);
		if (isset($this->groupData['groupIDs']) && $this->groupData['groupIDs'] != $groupIDs) {
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
	 * @param	\wcf\data\userUser		$user
	 * @param	boolean				$hideSession	if true, database won't be updated
	 */
	public function changeUser(User $user, $hideSession = false) {
		$eventParameters = array('user' => $user, 'hideSession' => $hideSession); 
		
		EventHandler::getInstance()->fireAction($this, 'beforeChangeUser', $eventParameters);
		
		$user = $eventParameters['user']; 
		$hideSession = $eventParameters['hideSession'];
		
		// skip changeUserVirtual, if session will not be persistent anyway
		if ($this->supportsVirtualSessions && !$hideSession) {
			$this->changeUserVirtual($user);
		}
		else {
			// update user reference
			$this->user = $user;
			
			if (!$hideSession) {
				// update session
				$sessionEditor = new $this->sessionEditorClassName($this->session);
				
				$this->register('__changeSessionID', true);
				
				try {
					$sessionEditor->update(array(
						'userID' => $user->userID
					));
				}
				catch (DatabaseException $e) {
					// MySQL error 23000 = unique key
					// do not check against the message itself, some weird systems localize them
					if ($e->getCode() == 23000) {
						$sessionTable = call_user_func(array($this->sessionClassName, 'getDatabaseTableName'));
						
						// user is not a guest, delete all other sessions of this user
						$sql = "DELETE FROM	".$sessionTable."
							WHERE		sessionID <> ?
									AND userID = ?";
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute(array($this->sessionID, $user->userID));
						
						// update session
						$sessionEditor->update(array(
							'userID' => $user->userID
						));
					}
					else {
						// not our business
						throw $e;
					}
				}
			}
		}
		
		// reset caches
		$this->groupData = null;
		$this->languageIDs = null;
		$this->languageID = $this->user->languageID;
		$this->styleID = $this->user->styleID;
		
		EventHandler::getInstance()->fireAction($this, 'afterChangeUser');
	}
	
	/**
	 * Changes the user stored in the session, this method is different from changeUser() because it
	 * attempts to re-use sessions unless there are other virtual sessions for the same user (userID != 0).
	 * In reverse, logging out attempts to re-use the current session or spawns a new session depending
	 * on other virtual sessions.
	 * 
	 * @param	\wcf\data\user\User	$user
	 */
	protected function changeUserVirtual(User $user) {
		$sessionTable = call_user_func(array($this->sessionClassName, 'getDatabaseTableName'));
		
		switch ($user->userID) {
			//
			// user -> guest (logout)
			//
			case 0:
				// delete virtual session
				if ($this->virtualSession) {
					$virtualSessionEditor = new SessionVirtualEditor($this->virtualSession);
					$virtualSessionEditor->delete();
				}
				
				// there are still other virtual sessions, create a new session
				if (SessionVirtual::countVirtualSessions($this->session->sessionID)) {
					// save session
					$sessionData = array(
						'sessionID' => StringUtil::getRandomID(),
						'userID' => $user->userID,
						'ipAddress' => UserUtil::getIpAddress(),
						'userAgent' => UserUtil::getUserAgent(),
						'lastActivityTime' => TIME_NOW,
						'requestURI' => UserUtil::getRequestURI(),
						'requestMethod' => (!empty($_SERVER['REQUEST_METHOD']) ? substr($_SERVER['REQUEST_METHOD'], 0, 7) : '')
					);
					
					$this->session = call_user_func(array($this->sessionEditorClassName, 'create'), $sessionData);
					
					HeaderUtil::setCookie('cookieHash', $this->session->sessionID);
				}
				else {
					// this was the last virtual session, re-use current session
					// update session
					$sessionEditor = new $this->sessionEditorClassName($this->session);
					$sessionEditor->update(array(
						'userID' => $user->userID
					));
				}
			break;
			
			//
			// guest -> user (login)
			//
			default:
				// find existing session for this user
				$session = call_user_func(array($this->sessionClassName, 'getSessionByUserID'), $user->userID);
				
				// no session exists, re-use current session
				if ($session === null) {
					// update session
					$sessionEditor = new $this->sessionEditorClassName($this->session);
					
					try {
						$this->register('__changeSessionID', true);
						
						$sessionEditor->update(array(
							'userID' => $user->userID
						));
					}
					catch (DatabaseException $e) {
						// MySQL error 23000 = unique key
						// do not check against the message itself, some weird systems localize them
						if ($e->getCode() == 23000) {
							// delete guest session
							$sessionEditor = new $this->sessionEditorClassName($this->session);
							$sessionEditor->delete();
							
							// inherit existing session
							$this->session = $session;
						}
						else {
							// not our business
							throw $e;
						}
					}
				}
				else {
					// delete guest session
					$sessionEditor = new $this->sessionEditorClassName($this->session);
					$sessionEditor->delete();
					
					// inherit existing session
					$this->session = $session;
				}
			break;
		}
		
		$this->user = $user;
		$this->loadVirtualSession(true);
	}
	
	/**
	 * Updates user session on shutdown.
	 */
	public function update() {
		if ($this->doNotUpdate) return;
		
		// set up data
		$data = array(
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' => $this->userAgent,
			'requestURI' => $this->requestURI,
			'requestMethod' => $this->requestMethod,
			'lastActivityTime' => TIME_NOW
		);
		if (!class_exists('wcf\system\CLIWCF', false) && PACKAGE_ID && RequestHandler::getInstance()->getActiveRequest() && RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof ITrackablePage && RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->isTracked()) {
			$data['controller'] = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->getController();
			$data['parentObjectType'] = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->getParentObjectType();
			$data['parentObjectID'] = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->getParentObjectID();
			$data['objectType'] = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->getObjectType();
			$data['objectID'] = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->getObjectID();
		}
		if ($this->variablesChanged) {
			$data['sessionVariables'] = serialize($this->variables);
		}
		
		// update session
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->update($data);
		
		if ($this->virtualSession instanceof SessionVirtual) {
			$virtualSessionEditor = new SessionVirtualEditor($this->virtualSession);
			$virtualSessionEditor->updateLastActivityTime();
		}
	}
	
	/**
	 * Updates last activity time to protect session from expiring.
	 */
	public function keepAlive() {
		$this->disableUpdate();
		
		// update last activity time
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->update(array(
			'lastActivityTime' => TIME_NOW
		));
		
		if ($this->virtualSession instanceof SessionVirtual) {
			$virtualSessionEditor = new SessionVirtualEditor($this->virtualSession);
			$virtualSessionEditor->updateLastActivityTime();
		}
	}
	
	/**
	 * Deletes this session and it's related data.
	 */
	public function delete() {
		// clear storage
		if ($this->user->userID) {
			self::resetSessions(array($this->user->userID));
			
			// update last activity time
			if (!class_exists('\wcf\system\WCFACP', false)) {
				$editor = new UserEditor($this->user);
				$editor->update(array('lastActivityTime' => TIME_NOW));
			}
		}
		
		// 1st: Change user to guest, otherwise other the entire session, including
		// all virtual sessions of the user will be deleted
		$this->changeUser(new User(null));
		
		// 2nd: Actually remove session
		$sessionEditor = new $this->sessionEditorClassName($this->session);
		$sessionEditor->delete();
		
		// disable update
		$this->disableUpdate();
	}
	
	/**
	 * Returns currently active language id.
	 * 
	 * @return	integer
	 */
	public function getLanguageID() {
		return $this->languageID;
	}
	
	/**
	 * Sets the currently active language id.
	 * 
	 * @param	integer		$languageID
	 */
	public function setLanguageID($languageID) {
		$this->languageID = $languageID;
		$this->register('languageID', $this->languageID);
	}
	
	/**
	 * Returns currently active style id.
	 * 
	 * @return	integer
	 */
	public function getStyleID() {
		return $this->styleID;
	}
	
	/**
	 * Sets the currently active style id.
	 * 
	 * @param	integer		$styleID
	 */
	public function setStyleID($styleID) {
		$this->styleID = $styleID;
		$this->register('styleID', $this->styleID);
	}
	
	/**
	 * Resets session-specific storage data.
	 * 
	 * @param	array<integer>	$userIDs
	 */
	public static function resetSessions(array $userIDs = array()) {
		if (!empty($userIDs)) {
			UserStorageHandler::getInstance()->reset($userIDs, 'groupIDs', 1);
			UserStorageHandler::getInstance()->reset($userIDs, 'languageIDs', 1);
		}
		else {
			UserStorageHandler::getInstance()->resetAll('groupIDs', 1);
			UserStorageHandler::getInstance()->resetAll('languageIDs', 1);
		}
	}
	
	/**
	 * Returns the spider id for given user agent.
	 * 
	 * @param	string		$userAgent
	 * @return	mixed
	 */
	protected function getSpiderID($userAgent) {
		$spiderList = SpiderCacheBuilder::getInstance()->getData();
		$userAgent = strtolower($userAgent);
		
		foreach ($spiderList as $spider) {
			if (strpos($userAgent, $spider->spiderIdentifier) !== false) {
				return $spider->spiderID;
			}
		}
		
		return null;
	}
	
	/**
	 * Searches for existing session of a search spider.
	 * 
	 * @param	integer		$spiderID
	 * @return	\wcf\data\session\Session
	 */
	protected function getExistingSpiderSession($spiderID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_session
			WHERE	spiderID = ?
				AND userID IS NULL";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($spiderID));
		$row = $statement->fetchArray();
		if ($row !== false) {
			// fix session validation
			$row['ipAddress'] = UserUtil::getIpAddress();
			$row['userAgent'] = UserUtil::getUserAgent();
			
			// return session object
			return new $this->sessionClassName(null, $row);
		}
		
		return null;
	}
	
	/**
	 * Returns true if this is a new session.
	 * 
	 * @return	boolean
	 */
	public function isFirstVisit() {
		return $this->firstVisit;
	}
}
