<?php
namespace wcf\system\session;
use wcf\data\session\Session;
use wcf\data\session\SessionEditor;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\builder\SpiderCacheBuilder;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\cache\builder\UserGroupPermissionCacheBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\CryptoUtil;
use wcf\util\HeaderUtil;
use wcf\util\UserUtil;

/**
 * Handles sessions.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Session
 *
 * @property-read	string		$sessionID		unique textual identifier of the session
 * @property-read	integer|null	$userID			id of the user the session belongs to or `null` if the acp session belongs to a guest
 * @property-read	string		$ipAddress		id of the user whom the session belongs to
 * @property-read	string		$userAgent		user agent of the user whom the session belongs to
 * @property-read	integer		$lastActivityTime	timestamp at which the latest activity occurred
 * @property-read	string		$requestURI		uri of the latest request
 * @property-read	string		$requestMethod		used request method of the latest request (`GET`, `POST`)
 * @property-read	integer|null	$pageID			id of the latest page visited
 * @property-read	integer|null	$pageObjectID		id of the object the latest page visited belongs to
 * @property-read	integer|null	$parentPageID		id of the parent page of latest page visited
 * @property-read	integer|null	$parentPageObjectID	id of the object the parent page of latest page visited belongs to
 * @property-read	integer		$spiderID		id of the spider the session belongs to
 */
final class SessionHandler extends SingletonFactory {
	/**
	 * prevents update on shutdown
	 * @var	boolean
	 */
	protected $doNotUpdate = false;
	
	/**
	 * disables page tracking
	 * @var	boolean
	 */
	protected $disableTracking = false;
	
	/**
	 * various environment variables
	 * @var	array
	 */
	protected $environment = [];
	
	/**
	 * group data and permissions
	 * @var	mixed[][]
	 */
	protected $groupData = null;
	
	/**
	 * true if within ACP or WCFSetup
	 * @var boolean
	 */
	protected $isACP = false;
	
	/**
	 * language id for active user
	 * @var	integer
	 */
	protected $languageID = 0;
	
	/**
	 * language ids for active user
	 * @var	integer[]
	 */
	protected $languageIDs = null;
	
	/**
	 * @var	string
	 */
	private $sessionID;
	
	/**
	 * session object
	 * @var	\wcf\data\acp\session\ACPSession
	 */
	protected $session = null;
	
	/**
	 * @var \wcf\data\session\Session
	 */
	protected $legacySession = null;
	
	/**
	 * style id
	 * @var	integer
	 */
	protected $styleID = null;
	
	/**
	 * user object
	 * @var	User
	 */
	protected $user = null;
	
	/**
	 * session variables
	 * @var	array
	 */
	protected $variables = [];
	
	/**
	 * indicates if session variables changed and must be saved upon shutdown
	 * @var	boolean
	 */
	protected $variablesChanged = false;
	
	/**
	 * true if this is a new session
	 * @var	boolean
	 */
	protected $firstVisit = false;
	
	/**
	 * list of names of permissions only available for users
	 * @var	string[]
	 */
	protected $usersOnlyPermissions = [];
	
	private const ACP_SESSION_LIFETIME = 7200;
	private const GUEST_SESSION_LIFETIME = 7200;
	private const USER_SESSION_LIFETIME = 86400 * 14;
	
	/**
	 * Provides access to session data.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function __get($key) {
		if ($key === 'sessionID') {
			return $this->sessionID;
		}
		else if ($key === 'userID') {
			return $this->user->userID;
		}
		// TODO: pageID, pageObjectID, parentPageID, parentPageObjectID
		
		if (array_key_exists($key, $this->environment)) {
			return $this->environment[$key];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->isACP = (class_exists(WCFACP::class, false) || !PACKAGE_ID);
		$this->usersOnlyPermissions = UserGroupOptionCacheBuilder::getInstance()->getData([], 'usersOnlyOptions');
	}
	
	/**
	 * @deprecated 5.4 - This method is a noop. The cookie suffix is determined automatically.
	 */
	public function setCookieSuffix() { }
	
	/**
	 * @deprecated 5.4 - This method is a noop. Cookie handling works automatically.
	 */
	public function setHasValidCookie($hasValidCookie) { }
	
	/**
	 * Returns the session ID stored in the session cookie or `null`.
	 */
	private function getSessionIdFromCookie(): ?string {
		$cookieName = COOKIE_PREFIX.($this->isACP ? 'acp' : 'user')."_session";
		
		if (isset($_COOKIE[$cookieName])) {
			if (!PACKAGE_ID) {
				return $_COOKIE[$cookieName];
			}
			
			return CryptoUtil::getValueFromSignedString($_COOKIE[$cookieName]);
		}
		
		return null;
	}
	
	/**
	 * Returns the signed session ID for use in a cookie.
	 */
	private function getSessionIdForCookie(string $sessionID): string {
		if (!PACKAGE_ID) {
			return $sessionID;
		}
		
		return CryptoUtil::createSignedString($sessionID);
	}
	
	/**
	 * Returns true if client provided a valid session cookie.
	 * 
	 * @return	boolean
	 * @since	3.0
	 */
	public function hasValidCookie(): bool {
		return $this->getSessionIdFromCookie() === $this->sessionID;
	}
	
	/**
	 * @deprecated 5.4 - Sessions are managed automatically. Use loadFromCookie().
	 */
	public function load($sessionEditorClassName, $sessionID) {
		$hasSession = false;
		if (!empty($sessionID)) {
			$hasSession = $this->getExistingSession($sessionID);
		}
		
		// create new session
		if (!$hasSession) {
			$this->create();
		}
	}
	
	/**
	 * Loads the session matching the session cookie.
	 */
	public function loadFromCookie() {
		$sessionID = $this->getSessionIdFromCookie();
		
		$hasSession = false;
		if ($sessionID) {
			$hasSession = $this->getExistingSession($sessionID);
		}
		
		// create new session
		if (!$hasSession) {
			$this->create();
		}
	}
	
	/**
	 * Initializes session system.
	 */
	public function initSession() {
		// init session environment
		$this->initSecurityToken();
		
		$this->defineConstants();
		
		// assign language and style id
		$this->languageID = ($this->getVar('languageID') === null) ? $this->user->languageID : $this->getVar('languageID');
		$this->styleID = ($this->getVar('styleID') === null) ? $this->user->styleID : $this->getVar('styleID');
		
		// init environment variables
		$this->initEnvironment();
		
		// https://github.com/WoltLab/WCF/issues/2568
		if ($this->getVar('__wcfIsFirstVisit') === true) {
			$this->firstVisit = true;
			$this->unregister('__wcfIsFirstVisit');
		}
	}
	
	/**
	 * Initializes environment variables.
	 */
	protected function initEnvironment() {
		$this->environment = [
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' => UserUtil::getUserAgent(),
			'requestURI' => UserUtil::getRequestURI(),
			'requestMethod' => !empty($_SERVER['REQUEST_METHOD']) ? substr($_SERVER['REQUEST_METHOD'], 0, 7) : '',
			'spiderID' => $this->getSpiderID(UserUtil::getUserAgent()),
			'lastActivityTime' => TIME_NOW,
		];
	}
	
	/**
	 * Disables update on shutdown.
	 */
	public function disableUpdate() {
		$this->doNotUpdate = true;
	}
	
	/**
	 * Disables page tracking.
	 */
	public function disableTracking() {
		$this->disableTracking = true;
	}
	
	/**
	 * Defines global wcf constants related to session.
	 */
	protected function defineConstants() {
		// security token
		if (!defined('SECURITY_TOKEN')) define('SECURITY_TOKEN', $this->getSecurityToken());
		if (!defined('SECURITY_TOKEN_INPUT_TAG')) define('SECURITY_TOKEN_INPUT_TAG', '<input type="hidden" name="t" value="'.$this->getSecurityToken().'">');
	}
	
	/**
	 * Initializes security token.
	 */
	protected function initSecurityToken() {
		if ($this->getVar('__SECURITY_TOKEN') === null) {
			$this->register('__SECURITY_TOKEN', \bin2hex(\random_bytes(20)));
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
		return \hash_equals($this->getSecurityToken(), $token);
	}
	
	/**
	 * Registers a session variable.
	 * 
	 * @param	string		$key
	 * @param	mixed		$value
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
	 * Returns the value of a session variable or `null` if the session
	 * variable does not exist.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function getVar($key) {
		if (isset($this->variables[$key])) {
			return $this->variables[$key];
		}
		
		return null;
	}
	
	/**
	 * Returns the user object of this session.
	 * 
	 * @return	User	$user
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Tries to read existing session identified by the given session id. Returns whether
	 * a session could be found.
	 */
	protected function getExistingSession(string $sessionID): bool {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".($this->isACP ? 'acp' : 'user')."_session
			WHERE	sessionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$sessionID
		]);
		$row = $statement->fetchSingleRow();
		
		if (!$row) {
			return false;
		}
		
		// Check whether the session technically already expired.
		$lifetime =
			($this->isACP   ? self::ACP_SESSION_LIFETIME  :
			($row['userID'] ? self::USER_SESSION_LIFETIME :
			(                 self::GUEST_SESSION_LIFETIME)));
		if ($row['lastActivityTime'] < (TIME_NOW - $lifetime)) {
			return false;
		}
		
		$variables = @unserialize($row['sessionVariables']);
		// Check whether the session variables became corrupted.
		if (!is_array($variables)) {
			return false;
		}
		
		$this->sessionID = $sessionID;
		$this->user = new User($row['userID']);
		$this->variables = $variables;
		
		$sql = "UPDATE	wcf".WCF_N."_".($this->isACP ? 'acp' : 'user')."_session
			SET	ipAddress = ?,
				userAgent = ?,
				lastActivityTime = ?
			WHERE	sessionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			UserUtil::getIpAddress(),
			UserUtil::getUserAgent(),
			TIME_NOW,
			$this->sessionID,
		]);
		
		// Refresh cookie.
		if ($this->user->userID && !$this->isACP) {
			HeaderUtil::setCookie(
				($this->isACP ? 'acp' : 'user')."_session",
				$this->getSessionIdForCookie($this->sessionID),
				TIME_NOW + 86400 * 14
			);
		}
		
		// Fetch legacy session.
		if (!$this->isACP) {
			$condition = new PreparedStatementConditionBuilder();
			
			if ($row['userID']) {
				// The `userID IS NOT NULL` condition technically is redundant, but is added for
				// clarity and consistency with the guest case below.
				$condition->add('userID IS NOT NULL');
				$condition->add('userID = ?', [$row['userID']]);
			}
			else {
				$condition->add('userID IS NULL');
				$condition->add('(sessionID = ? OR spiderID = ?)', [
					$row['sessionID'],
					$this->getSpiderID(UserUtil::getUserAgent()),
				]);
			}
			
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_session
				".$condition;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($condition->getParameters());
			$this->legacySession = $statement->fetchSingleObject(Session::class);
			
			if (!$this->legacySession) {
				$this->createLegacySession();
			}
		}
		
		return true;
	}
	
	/**
	 * Creates a new session.
	 */
	protected function create() {
		$this->sessionID = \bin2hex(\random_bytes(20));
		
		// Create new session.
		$sql = "INSERT INTO wcf".WCF_N."_".($this->isACP ? 'acp' : 'user')."_session
				(sessionID, ipAddress, userAgent, lastActivityTime, sessionVariables)
			VALUES
				(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->sessionID,
			UserUtil::getIpAddress(),
			UserUtil::getUserAgent(),
			TIME_NOW,
			serialize([]),
		]);
		
		HeaderUtil::setCookie(
			($this->isACP ? 'acp' : 'user')."_session",
			$this->getSessionIdForCookie($this->sessionID)
		);
		
		$this->variables = [];
		$this->user = new User(null);
		$this->firstVisit = true;
		
		// Maintain legacy session table for users online list.
		if (!$this->isACP) {
			$this->createLegacySession();
		}
	}
	
	private function createLegacySession() {
		$spiderID = $this->getSpiderID(UserUtil::getUserAgent());
		
		// save session
		$sessionData = [
			'sessionID' => $this->sessionID,
			'userID' => $this->user->userID,
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' => UserUtil::getUserAgent(),
			'lastActivityTime' => TIME_NOW,
			'requestURI' => UserUtil::getRequestURI(),
			'requestMethod' => !empty($_SERVER['REQUEST_METHOD']) ? substr($_SERVER['REQUEST_METHOD'], 0, 7) : ''
		];
		
		if ($spiderID !== null) $sessionData['spiderID'] = $spiderID;
		
		$this->legacySession = SessionEditor::create($sessionData);
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
	 * Returns true if a permission was set to 'Never'. This is required to preserve
	 * compatibility, while preventing ACLs from overruling a 'Never' setting.
	 * 
	 * @param       string          $permission
	 * @return      boolean
	 */
	public function getNeverPermission($permission) {
		$this->loadGroupData();
		
		return (isset($this->groupData['__never'][$permission]));
	}
	
	/**
	 * Checks if the active user has the given permissions and throws a
	 * PermissionDeniedException if that isn't the case.
	 * 
	 * @param	string[]	$permissions	list of permissions where each one must pass
	 * @throws	PermissionDeniedException
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
			$sql = "SELECT	groupID
				FROM	wcf".WCF_N."_user_to_group
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->user->userID]);
			$groupIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		else {
			$groupIDs = $this->user->getGroupIDs();
		}
		
		// get group data from cache
		$this->groupData = UserGroupPermissionCacheBuilder::getInstance()->getData($groupIDs);
		if (isset($this->groupData['groupIDs']) && $this->groupData['groupIDs'] != $groupIDs) {
			$this->groupData = [];
		}
	}
	
	/**
	 * Returns language ids for active user.
	 * 
	 * @return	integer[]
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
		
		$this->languageIDs = [];
		
		if (!$this->user->userID) {
			return;
		}
		
		// work-around for setup process (package wcf does not exist yet)
		if (!PACKAGE_ID) {
			$sql = "SELECT	languageID
				FROM	wcf".WCF_N."_user_to_language
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->user->userID]);
			$this->languageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
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
	 * @param	boolean		$hideSession	if true, database won't be updated
	 */
	public function changeUser(User $user, $hideSession = false) {
		$eventParameters = ['user' => $user, 'hideSession' => $hideSession];
		
		EventHandler::getInstance()->fireAction($this, 'beforeChangeUser', $eventParameters);
		
		$user = $eventParameters['user'];
		$hideSession = $eventParameters['hideSession'];
		
		// skip changeUserVirtual, if session will not be persistent anyway
		if (!$hideSession) {
			$this->changeUserVirtual($user);
		}
		
		// update user reference
		$this->user = $user;
		
		// reset caches
		$this->groupData = null;
		$this->languageIDs = null;
		$this->languageID = $this->user->languageID;
		$this->styleID = $this->user->styleID;
		
		// change language
		WCF::setLanguage($this->languageID ?: 0);
		
		// in some cases the language id can be stuck in the session variables
		$this->unregister('languageID');
		
		EventHandler::getInstance()->fireAction($this, 'afterChangeUser');
	}
	
	/**
	 * Changes the user stored in the session.
	 * 
	 * @param	User	$user
	 * @throws	DatabaseException
	 */
	protected function changeUserVirtual(User $user) {
		// We must delete the old session to not carry over any state across different users.
		$this->delete();
		
		// If the target user is a registered user ...
		if ($user->userID) {
			// ... we create a new session with a new session ID ...
			$this->create();
			
			// ... delete the newly created legacy session ...
			if (!$this->isACP) {
				$sql = "DELETE FROM wcf".WCF_N."_session
					WHERE	sessionID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$this->sessionID]);
			}
			
			// ... perform the login ...
			$sql = "UPDATE	wcf".WCF_N."_".($this->isACP ? 'acp' : 'user')."_session
				SET	userID = ?
				WHERE	sessionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$user->userID,
				$this->sessionID,
			]);
			
			// ... and reload the session with the updated information.
			$hasSession = $this->getExistingSession($this->sessionID);
			
			if (!$hasSession) {
				throw new \LogicException('Unreachable');
			}
		}
	}
	
	/**
	 * Updates user session on shutdown.
	 */
	public function update() {
		if ($this->doNotUpdate) return;
		
		if ($this->variablesChanged) {
			$sql = "UPDATE	wcf".WCF_N."_".($this->isACP ? 'acp' : 'user')."_session
				SET	sessionVariables = ?
				WHERE	sessionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				serialize($this->variables),
				$this->sessionID,
			]);
			
			// Reset the flag, because the variables are no longer dirty.
			$this->variablesChanged = false;
		}
		
		if (!$this->isACP) {
			$data = [
				'ipAddress' => $this->ipAddress,
				'userAgent' => $this->userAgent,
				'requestURI' => $this->requestURI,
				'requestMethod' => $this->requestMethod,
				'lastActivityTime' => TIME_NOW,
				'userID' => $this->user->userID,
				'sessionID' => $this->sessionID,
			];
			if (!class_exists('wcf\system\CLIWCF', false) && !$this->disableTracking) {
				$pageLocations = PageLocationManager::getInstance()->getLocations();
				if (isset($pageLocations[0])) {
					$data['pageID'] = $pageLocations[0]['pageID'];
					$data['pageObjectID'] = ($pageLocations[0]['pageObjectID'] ?: null);
					$data['parentPageID'] = null;
					$data['parentPageObjectID'] = null;
					
					for ($i = 1, $length = count($pageLocations); $i < $length; $i++) {
						if (!empty($pageLocations[$i]['useAsParentLocation'])) {
							$data['parentPageID'] = $pageLocations[$i]['pageID'];
							$data['parentPageObjectID'] = ($pageLocations[$i]['pageObjectID'] ?: null);
							break;
						}
					}
				}
			}
			
			if ($this->legacySession) {
				$sessionEditor = new SessionEditor($this->legacySession);
				$sessionEditor->update($data);
			}
		}
	}
	
	/**
	 * @deprecated 5.4 - This method is a noop. The lastActivityTime is always updated immediately after loading.
	 */
	public function keepAlive() { }
	
	/**
	 * Deletes this session and its related data.
	 */
	public function delete() {
		// clear storage
		if ($this->user->userID) {
			self::resetSessions([$this->user->userID]);
			
			// update last activity time
			if (!$this->isACP) {
				$editor = new UserEditor($this->user);
				$editor->update(['lastActivityTime' => TIME_NOW]);
			}
		}
		
		// Delete session.
		$sql = "DELETE FROM wcf".WCF_N."_".($this->isACP ? 'acp' : 'user')."_session
			WHERE	sessionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->sessionID]);
		
		// Delete legacy session.
		if (!$this->isACP) {
			$sql = "DELETE FROM wcf".WCF_N."_session
				WHERE	sessionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->sessionID]);
		}
	}
	
	/**
	 * Prunes expired sessions.
	 */
	public function prune() {
		// Prevent the sessions from expiring while the development mode is active.
		if (!ENABLE_DEBUG_MODE || !ENABLE_DEVELOPER_TOOLS) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_session
				WHERE		lastActivityTime < ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				TIME_NOW - self::ACP_SESSION_LIFETIME,
			]);
		}
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_session
			WHERE		(lastActivityTime < ? AND userID IS NULL)
				OR	(lastActivityTime < ? AND userID IS NOT NULL)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			TIME_NOW - self::GUEST_SESSION_LIFETIME,
			TIME_NOW - self::USER_SESSION_LIFETIME,
		]);
		
		// Legacy sessions live 120 minutes, they will be re-created on demand.
		$sql = "DELETE FROM	wcf".WCF_N."_session
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			TIME_NOW - (3600 * 2),
		]);
	}
	
	/**
	 * Deletes this session if:
	 * - it is newly created in this request, and
	 * - it belongs to a guest.
	 * 
	 * This method is useful if you have controllers that are likely to be
	 * accessed by a user agent that is not going to re-use sessions (e.g.
	 * curl in a cronjob). It immediately remove the session that was created
	 * just for that request and that is not going to be used ever again.
	 * 
	 * @since 5.2
	 */
	public function deleteIfNew() {
		if ($this->isFirstVisit() && !$this->getUser()->userID) {
			$this->delete();
		}
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
	 * @param	integer[]	$userIDs
	 */
	public static function resetSessions(array $userIDs = []) {
		if (!empty($userIDs)) {
			UserStorageHandler::getInstance()->reset($userIDs, 'groupIDs');
			UserStorageHandler::getInstance()->reset($userIDs, 'languageIDs');
		}
		else {
			UserStorageHandler::getInstance()->resetAll('groupIDs');
			UserStorageHandler::getInstance()->resetAll('languageIDs');
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
	 * Returns true if this is a new session.
	 * 
	 * @return	boolean
	 */
	public function isFirstVisit() {
		return $this->firstVisit;
	}
}
