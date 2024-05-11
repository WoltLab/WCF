<?php

namespace wcf\system\session;

use ParagonIE\ConstantTime\Hex;
use wcf\data\session\Session as LegacySession;
use wcf\data\session\SessionEditor;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\session\PreserveVariablesCollecting;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\cache\builder\UserGroupPermissionCacheBuilder;
use wcf\system\database\exception\DatabaseQueryExecutionException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\request\RouteHandler;
use wcf\system\SingletonFactory;
use wcf\system\spider\SpiderHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\CryptoUtil;
use wcf\util\HeaderUtil;
use wcf\util\UserUtil;

/**
 * Handles sessions.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   string $sessionID      unique textual identifier of the session
 * @property-read   int|null $userID         id of the user the session belongs to or `null` if the session belongs to a guest
 * @property-read   int|null $pageID         id of the latest page visited
 * @property-read   int|null $pageObjectID       id of the object the latest page visited belongs to
 * @property-read   int|null $parentPageID       id of the parent page of latest page visited
 * @property-read   int|null $parentPageObjectID id of the object the parent page of latest page visited belongs to
 * @property-read   ?string $spiderIdentifier       identifier of the spider
 */
final class SessionHandler extends SingletonFactory
{
    /**
     * prevents update on shutdown
     */
    private bool $doNotUpdate = false;

    /**
     * disables page tracking
     */
    private bool $disableTracking = false;

    /**
     * group data and permissions
     * @var mixed[][]
     */
    private $groupData;

    /**
     * true if within ACP or WCFSetup
     */
    private bool $isACP = false;

    /**
     * language id for active user
     * @var int
     */
    private $languageID = 0;

    /**
     * @var string
     */
    private string $sessionID;

    private ?LegacySession $legacySession = null;

    /**
     * user object
     * @var User
     */
    private User $user;

    /**
     * session variables
     * @var array
     */
    private $variables = [];

    /**
     * indicates if session variables changed and must be saved upon shutdown
     */
    private bool $variablesChanged = false;

    private bool $firstVisit = false;

    /**
     * list of names of permissions only available for users
     * @var string[]
     */
    private $usersOnlyPermissions = [];

    private string $xsrfToken;

    private const GUEST_SESSION_LIFETIME = 2 * 3600;

    private const USER_SESSION_LIFETIME = 60 * 86400;

    private const USER_SESSION_LIMIT = 30;

    private const CHANGE_USER_AFTER_MULTIFACTOR_KEY = self::class . "\0__changeUserAfterMultifactor__";

    private const PENDING_USER_LIFETIME = 15 * 60;

    private const REAUTHENTICATION_KEY = self::class . "\0__reauthentication__";

    private const REAUTHENTICATION_HARD_LIMIT = 12 * 3600;

    private const REAUTHENTICATION_SOFT_LIMIT = 2 * 3600;

    private const REAUTHENTICATION_SOFT_LIMIT_ACP = 2 * 3600;

    private const REAUTHENTICATION_GRACE_PERIOD = 15 * 60;

    /**
     * Provides access to session data.
     *
     * @return  mixed
     */
    public function __get(string $key)
    {
        switch ($key) {
            case 'sessionID':
                return $this->sessionID;
            case 'userID':
                return $this->user->userID;
            case 'spiderIdentifier':
                if ($this->userID) {
                    return null;
                }

                if ($this->isACP) {
                    return null;
                }

                return $this->legacySession->spiderIdentifier;
            case 'pageID':
            case 'pageObjectID':
            case 'parentPageID':
            case 'parentPageObjectID':
                return $this->legacySession->{$key} ?? null;

                /** @deprecated 5.4 - The below values are deprecated. */
            case 'ipAddress':
                return UserUtil::getIpAddress();
            case 'userAgent':
                return UserUtil::getUserAgent();
            case 'requestURI':
                return UserUtil::getRequestURI();
            case 'requestMethod':
                return !empty($_SERVER['REQUEST_METHOD']) ? \substr($_SERVER['REQUEST_METHOD'], 0, 7) : '';
            case 'lastActivityTime':
                return TIME_NOW;

            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->isACP = (\class_exists(WCFACP::class, false) || !PACKAGE_ID);
        $this->usersOnlyPermissions = UserGroupOptionCacheBuilder::getInstance()->getData([], 'usersOnlyOptions');
    }

    /**
     * Parses the session cookie value, returning an array with the stored fields.
     *
     * The return array is guaranteed to have a `sessionId` key.
     */
    private function parseCookie(string $value): array
    {
        $length = \mb_strlen($value, '8bit');
        if ($length < 1) {
            throw new \InvalidArgumentException(\sprintf(
                'Expected at least 1 Byte, %d given.',
                $length
            ));
        }

        $version = \unpack('Cversion', $value)['version'];
        if (!\in_array($version, [1], true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Unknown version %d',
                $version
            ));
        }

        if ($version === 1) {
            if ($length !== 22) {
                throw new \InvalidArgumentException(\sprintf(
                    'Expected exactly 22 Bytes, %d given.',
                    $length
                ));
            }
            $data = \unpack('Cversion/a20sessionId/Ctimestep', $value);
            \assert($data['version'] === 1);
            \assert(\strlen($data['sessionId']) === 20);
            $data['sessionId'] = Hex::encode($data['sessionId']);

            return $data;
        }

        throw new \LogicException('Unreachable');
    }

    /**
     * Extracts the data from the session cookie.
     *
     * @see SessionHandler::parseCookie()
     * @since 5.4
     */
    private function getParsedCookieData(): ?array
    {
        $cookieName = COOKIE_PREFIX . "user_session";

        if (!empty($_COOKIE[$cookieName])) {
            if (!PACKAGE_ID) {
                return [
                    'sessionId' => $_COOKIE[$cookieName],
                ];
            }

            $cookieData = CryptoUtil::getValueFromSignedString($_COOKIE[$cookieName]);

            // Check whether the sessionId was correctly signed.
            if ($cookieData === null) {
                return null;
            }

            try {
                return $this->parseCookie($cookieData);
            } catch (\InvalidArgumentException $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Returns the session ID stored in the session cookie or `null`.
     */
    private function getSessionIdFromCookie(?array $cookieData): ?string
    {
        if ($cookieData !== null) {
            return $cookieData['sessionId'];
        }

        return null;
    }

    /**
     * Returns the current time step. The time step changes
     * every 24 hours.
     */
    private function getCookieTimestep(): int
    {
        $window = (24 * 3600);

        \assert((self::USER_SESSION_LIFETIME / $window) < 0xFF);

        return \intdiv(TIME_NOW, $window) & 0xFF;
    }

    /**
     * Returns the signed session data for use in a cookie.
     */
    private function getCookieValue(): string
    {
        if (!PACKAGE_ID) {
            return $this->sessionID;
        }

        return CryptoUtil::createSignedString(\pack(
            'CA20C',
            1,
            Hex::decode($this->sessionID),
            $this->getCookieTimestep()
        ));
    }

    /**
     * Returns true if client provided a valid session cookie.
     *
     * @since   3.0
     */
    public function hasValidCookie(): bool
    {
        return $this->getSessionIdFromCookie($this->getParsedCookieData()) === $this->sessionID;
    }

    /**
     * @deprecated 5.4 - Sessions are managed automatically. Use loadFromCookie().
     */
    public function load($sessionEditorClassName, $sessionID)
    {
        $hasSession = false;
        if (!empty($sessionID)) {
            $hasSession = $this->getExistingSession($sessionID);
        }

        if (!$hasSession) {
            $this->create();
        }
    }

    /**
     * Loads the session matching the session cookie.
     */
    public function loadFromCookie()
    {
        $cookieData = $this->getParsedCookieData();
        $sessionID = $this->getSessionIdFromCookie($cookieData);

        $hasSession = false;
        if ($sessionID) {
            $hasSession = $this->getExistingSession($sessionID);
        }

        if ($hasSession) {
            $this->maybeRefreshCookie($cookieData);
        } else {
            $this->create();
        }
    }

    /**
     * Refreshes the session cookie, extending the expiry.
     */
    private function maybeRefreshCookie(array $cookieData): void
    {
        // Guests use short-lived sessions with an actual session cookie.
        if (!$this->user->userID) {
            return;
        }

        // No refresh is needed if the timestep matches up.
        if (isset($cookieData['timestep']) && $cookieData['timestep'] === $this->getCookieTimestep()) {
            return;
        }

        // Refresh the cookie.
        HeaderUtil::setCookie(
            'user_session',
            $this->getCookieValue(),
            TIME_NOW + (self::USER_SESSION_LIFETIME + (7 * 86400))
        );
    }

    /**
     * Initializes session system.
     */
    public function initSession()
    {
        // assign language
        $this->languageID = $this->getVar('languageID') ?: $this->user->languageID;

        // https://github.com/WoltLab/WCF/issues/2568
        if ($this->getVar('__wcfIsFirstVisit') === true) {
            $this->firstVisit = true;
            $this->unregister('__wcfIsFirstVisit');
        }
    }

    /**
     * Disables update on shutdown.
     */
    public function disableUpdate()
    {
        $this->doNotUpdate = true;
    }

    /**
     * Disables page tracking.
     */
    public function disableTracking(): void
    {
        $this->disableTracking = true;
    }

    /**
     * Initializes security token.
     */
    private function initSecurityToken(): void
    {
        $xsrfToken = '';
        if (!empty($_COOKIE['XSRF-TOKEN'])) {
            // We intentionally do not extract the signed value and instead just verify the correctness.
            //
            // The reason is that common JavaScript frameworks can use the contents of the `XSRF-TOKEN` cookie as-is,
            // without performing any processing on it, improving interoperability. Leveraging this JavaScript framework
            // feature requires the author of the controller to check the value within the `X-XSRF-TOKEN` request header
            // instead of the WoltLab Suite specific `t` parameter, though.
            //
            // The only reason we sign the cookie is that an XSS vulnerability or a rogue application on a subdomain
            // is not able to create a valid `XSRF-TOKEN`, e.g. by setting the `XSRF-TOKEN` cookie to the static
            // value `1234`, possibly allowing later exploitation.
            if (
                !PACKAGE_ID
                || CryptoUtil::getValueFromSignedString($_COOKIE['XSRF-TOKEN']) !== null
            ) {
                $xsrfToken = $_COOKIE['XSRF-TOKEN'];
            }
        }

        if (!$xsrfToken) {
            if (PACKAGE_ID) {
                $xsrfToken = CryptoUtil::createSignedString(\random_bytes(16));
            } else {
                $xsrfToken = Hex::encode(\random_bytes(16));
            }

            // We construct the cookie manually instead of using HeaderUtil::setCookie(), because:
            // 1) We don't want the prefix. The `XSRF-TOKEN` cookie name is a standard name across applications
            //    and it is supported by default in common JavaScript frameworks.
            // 2) We want to set the SameSite=lax parameter.
            // 3) We don't want the HttpOnly parameter.

            $sameSite = '; SameSite=lax';

            // Workaround for WebKit Bug #255524.
            // https://bugs.webkit.org/show_bug.cgi?id=255524
            $sameSite = '';

            \header(
                'set-cookie: XSRF-TOKEN=' . \rawurlencode($xsrfToken) . '; path=/' . (RouteHandler::secureConnection() ? '; secure' : '') . $sameSite,
                false
            );
        }

        $this->xsrfToken = $xsrfToken;
    }

    /**
     * Returns security token.
     */
    public function getSecurityToken(): string
    {
        if (!isset($this->xsrfToken)) {
            $this->initSecurityToken();
        }

        return $this->xsrfToken;
    }

    /**
     * Validates the given security token, returns false if
     * given token is invalid.
     */
    public function checkSecurityToken(string $token): bool
    {
        // The output of CryptoUtil::createSignedString() is not url-safe. For compatibility
        // reasons the SECURITY_TOKEN in URLs might not be encoded, turning the '+' into a space.
        // Convert it back before comparing.
        $token = \str_replace(' ', '+', $token);

        return \hash_equals($this->getSecurityToken(), $token);
    }

    /**
     * Registers a session variable.
     */
    public function register(string $key, mixed $value): void
    {
        $scope = $this->isACP ? 'acp' : 'frontend';

        $this->variables[$scope][$key] = $value;
        $this->variablesChanged = true;
    }

    /**
     * Unsets a session variable.
     */
    public function unregister(string $key): void
    {
        $scope = $this->isACP ? 'acp' : 'frontend';

        unset($this->variables[$scope][$key]);
        $this->variablesChanged = true;
    }

    /**
     * Returns the value of a session variable or `null` if the session
     * variable does not exist.
     */
    public function getVar(string $key): mixed
    {
        $scope = $this->isACP ? 'acp' : 'frontend';

        return $this->variables[$scope][$key] ?? null;
    }

    /**
     * Returns the user object of this session.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Tries to read existing session identified by the given session id. Returns whether
     * a session could be found.
     */
    private function getExistingSession(string $sessionID): bool
    {
        $sql = "SELECT  *
                FROM    wcf1_user_session
                WHERE   sessionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $sessionID,
        ]);
        $row = $statement->fetchSingleRow();

        if (!$row) {
            return false;
        }

        // Check whether the session technically already expired.
        $lifetime = ($row['userID'] ? self::USER_SESSION_LIFETIME : self::GUEST_SESSION_LIFETIME);
        if ($row['lastActivityTime'] < (TIME_NOW - $lifetime)) {
            return false;
        }

        try {
            $variables = \unserialize($row['sessionVariables']);

            // Check whether the session variables became corrupted.
            if (!\is_array($variables)) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        $this->sessionID = $sessionID;
        $this->user = new User($row['userID']);
        $this->variables = $variables;

        // Update ipAddress, userAgent and lastActivityTime only once per minute to
        // reduce write traffic to the hot 'user_session' table.
        //
        // The former two fields are not going to rapidly change and the latter is just
        // used for session expiry, where accuracy to the second is not required.
        if ($row['lastActivityTime'] < (TIME_NOW - 60)) {
            $sql = "UPDATE  wcf1_user_session
                    SET     ipAddress = ?,
                            userAgent = ?,
                            lastActivityTime = ?
                    WHERE   sessionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                UserUtil::getIpAddress(),
                UserUtil::getUserAgent(),
                TIME_NOW,
                $this->sessionID,
            ]);
        }

        if (!$this->isACP) {
            // Fetch legacy session.
            $condition = new PreparedStatementConditionBuilder();

            if ($row['userID']) {
                // The `userID IS NOT NULL` condition technically is redundant, but is added for
                // clarity and consistency with the guest case below.
                $condition->add('userID IS NOT NULL');
                $condition->add('userID = ?', [$row['userID']]);
            } else {
                $condition->add('userID IS NULL');
                $condition->add('(sessionID = ? OR spiderIdentifier = ?)', [
                    $row['sessionID'],
                    SpiderHandler::getInstance()->getIdentifier(UserUtil::getUserAgent()),
                ]);
            }

            $sql = "SELECT  *
                    FROM    wcf1_session
                    {$condition}";
            $legacySessionStatement = WCF::getDB()->prepare($sql);
            $legacySessionStatement->execute($condition->getParameters());
            $this->legacySession = $legacySessionStatement->fetchSingleObject(LegacySession::class);

            if ($this->legacySession === null) {
                try {
                    $this->legacySession = $this->createLegacySession();
                } catch (DatabaseQueryExecutionException $e) {
                    // Creation of the legacy session might fail due to duplicate key errors for
                    // concurrent requests.
                    if ($e->getCode() == '23000' && $e->getDriverCode() == '1062') {
                        // Attempt to load the legacy session once again. If the legacy session for some
                        // reason *still* is null then we simply continue without a legacy session. It is
                        // not required for proper request processing and consumers of the values stored
                        // within the legacy session (`page*`) cannot rely on any (valid) values being stored
                        // anyway.
                        $legacySessionStatement->execute($condition->getParameters());
                        $this->legacySession = $legacySessionStatement->fetchSingleObject(LegacySession::class);
                    } else {
                        throw $e;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Creates a new session.
     */
    private function create(): void
    {
        $this->sessionID = Hex::encode(\random_bytes(20));

        $variables = [
            'frontend' => [],
            'acp' => [],
        ];

        // Create new session.
        $sql = "INSERT INTO wcf1_user_session
                            (sessionID, ipAddress, userAgent, creationTime, lastActivityTime, sessionVariables)
                VALUES      (?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->sessionID,
            UserUtil::getIpAddress(),
            UserUtil::getUserAgent(),
            TIME_NOW,
            TIME_NOW,
            \serialize($variables),
        ]);

        $this->variables = $variables;
        $this->user = new User(null);
        $this->firstVisit = true;

        HeaderUtil::setCookie(
            "user_session",
            $this->getCookieValue()
        );

        // Maintain legacy session table for users online list.
        $this->legacySession = null;

        if (!$this->isACP) {
            // Try to find an existing spider session. Order by lastActivityTime to maintain a
            // stable selection in case duplicates exist for some reason.
            $spiderIdentifier = SpiderHandler::getInstance()->getIdentifier(UserUtil::getUserAgent());
            if ($spiderIdentifier) {
                $sql = "SELECT      *
                        FROM        wcf1_session
                        WHERE       spiderIdentifier = ?
                                AND userID IS NULL
                        ORDER BY    lastActivityTime DESC";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$spiderIdentifier]);
                $this->legacySession = $statement->fetchSingleObject(LegacySession::class);
            }

            if ($this->legacySession === null) {
                $this->legacySession = $this->createLegacySession();
            }
        }
    }

    private function createLegacySession(): LegacySession
    {
        $spiderIdentifier = null;
        if (!$this->user->userID) {
            $spiderIdentifier = SpiderHandler::getInstance()->getIdentifier(UserUtil::getUserAgent());
        }

        // save session
        $sessionData = [
            'sessionID' => $this->sessionID,
            'userID' => $this->user->userID,
            'ipAddress' => UserUtil::getIpAddress(),
            'userAgent' => UserUtil::getUserAgent(),
            'lastActivityTime' => TIME_NOW,
            'requestURI' => UserUtil::getRequestURI(),
            'requestMethod' => !empty($_SERVER['REQUEST_METHOD']) ? \substr($_SERVER['REQUEST_METHOD'], 0, 7) : '',
            'spiderIdentifier' => $spiderIdentifier,
        ];

        return SessionEditor::create($sessionData);
    }

    /**
     * Returns the value of the permission with the given name.
     *
     * @return  mixed       permission value
     */
    public function getPermission(string $permission)
    {
        // check if a users only permission is checked for a guest and return
        // false if that is the case
        if (!$this->user->userID && \in_array($permission, $this->usersOnlyPermissions)) {
            return false;
        }

        $this->loadGroupData();

        if (!isset($this->groupData[$permission])) {
            return false;
        }

        return $this->groupData[$permission];
    }

    /**
     * Returns true if a permission was set to 'Never'. This is required to preserve
     * compatibility, while preventing ACLs from overruling a 'Never' setting.
     *
     * @return      bool
     */
    public function getNeverPermission(string $permission)
    {
        $this->loadGroupData();

        return isset($this->groupData['__never'][$permission]);
    }

    /**
     * Checks if the active user has the given permissions and throws a
     * PermissionDeniedException if that isn't the case.
     *
     * @param string[] $permissions list of permissions where each one must pass
     * @throws  PermissionDeniedException
     */
    public function checkPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->getPermission($permission)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Loads group data from cache.
     */
    private function loadGroupData()
    {
        if ($this->groupData !== null) {
            return;
        }

        // work-around for setup process (package wcf does not exist yet)
        if (!PACKAGE_ID) {
            $sql = "SELECT  groupID
                    FROM    wcf1_user_to_group
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->user->userID]);
            $groupIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $groupIDs = $this->user->getGroupIDs();
        }

        // get group data from cache
        $this->groupData = UserGroupPermissionCacheBuilder::getInstance()->getData($groupIDs);
    }

    /**
     * @deprecated 6.0 Use User::getLanguageIDs() instead.
     */
    public function getLanguageIDs()
    {
        if (!$this->user->userID) {
            return [];
        }

        return $this->user->getLanguageIDs();
    }

    /**
     * If multi-factor authentication is enabled for the given user then
     * - the userID will be stored in the session variables, and
     * - `true` is returned.
     * Otherwise,
     * - `changeUser()` will be called, and
     * - `false` is returned.
     *
     * If `true` is returned you should perform a redirect to `MultifactorAuthenticationForm`.
     *
     * @since 5.4
     */
    public function changeUserAfterMultifactorAuthentication(User $user): bool
    {
        if ($user->multifactorActive) {
            $this->register(self::CHANGE_USER_AFTER_MULTIFACTOR_KEY, [
                'userId' => $user->userID,
                'expires' => TIME_NOW + self::PENDING_USER_LIFETIME,
            ]);
            $this->setLanguageID($user->languageID);

            return true;
        } else {
            $this->changeUser($user);

            return false;
        }
    }

    /**
     * Applies the pending user change, calling `changeUser()` for the user returned
     * by `getPendingUserChange()`.
     *
     * As a safety check you must provide the `$expectedUser` as a parameter, it must match the
     * data stored within the session.
     *
     * @throws \RuntimeException If the `$expectedUser` does not match.
     * @throws \BadMethodCallException If `getPendingUserChange()` returns `null`.
     * @see SessionHandler::getPendingUserChange()
     * @since 5.4
     */
    public function applyPendingUserChange(User $expectedUser): void
    {
        $user = $this->getPendingUserChange();
        $this->clearPendingUserChange();

        if (!$user) {
            throw new \BadMethodCallException('No pending user change.');
        }

        if ($user->userID !== $expectedUser->userID) {
            throw new \RuntimeException('Mismatching expectedUser.');
        }

        $this->changeUser($user);
    }

    /**
     * Returns the pending user change initiated by `changeUserAfterMultifactorAuthentication()`.
     *
     * @see SessionHandler::changeUserAfterMultifactorAuthentication()
     * @since 5.4
     */
    public function getPendingUserChange(): ?User
    {
        $data = $this->getVar(self::CHANGE_USER_AFTER_MULTIFACTOR_KEY);
        if (!$data) {
            return null;
        }

        $userId = $data['userId'];
        $expires = $data['expires'];

        if ($expires < TIME_NOW) {
            return null;
        }

        $user = new User($userId);

        if (!$user->userID) {
            return null;
        }

        return $user;
    }

    /**
     * Clears a pending user change, reverses the effects of `changeUserAfterMultifactorAuthentication()`.
     *
     * @see SessionHandler::changeUserAfterMultifactorAuthentication()
     * @since 5.4
     */
    public function clearPendingUserChange(): void
    {
        $this->unregister(self::CHANGE_USER_AFTER_MULTIFACTOR_KEY);
    }

    /**
     * Stores a new user object in this session, e.g. a user was guest because not
     * logged in, after the login his old session is used to store his full data.
     *
     * @param $hideSession if true, database won't be updated
     */
    public function changeUser(User $user, bool $hideSession = false)
    {
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
        $this->languageID = $this->user->languageID;

        // change language
        WCF::setLanguage($this->languageID ?: 0);

        // in some cases the language id can be stuck in the session variables
        $this->unregister('languageID');

        EventHandler::getInstance()->fireAction($this, 'afterChangeUser');
    }

    /**
     * Changes the user stored in the session.
     *
     * @param User $user
     * @throws  DatabaseException
     */
    private function changeUserVirtual(User $user): void
    {
        $event = new PreserveVariablesCollecting();
        EventHandler::getInstance()->fire($event);

        $saveVars = [];
        foreach ($event->keys as $key) {
            if (!$this->getVar($key)) {
                continue;
            }

            $saveVars[$key] = $this->getVar($key);
        }

        // We must delete the old session to not carry over any state across different users.
        $this->delete();

        // If the target user is a registered user ...
        if ($user->userID) {
            // ... we create a new session with a new session ID ...
            $this->create();

            // ... delete the newly created legacy session ...
            $sql = "DELETE FROM wcf1_session
                    WHERE       sessionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->sessionID]);

            // ... perform the login ...
            $sql = "UPDATE  wcf1_user_session
                    SET     userID = ?
                    WHERE   sessionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $user->userID,
                $this->sessionID,
            ]);

            // ... delete any user sessions exceeding the limit ...
            $sql = "SELECT  all_sessions.sessionID
                    FROM    wcf1_user_session all_sessions
                    LEFT JOIN (
                        SELECT      sessionID
                        FROM        wcf1_user_session
                        WHERE       userID = ?
                        ORDER BY    lastActivityTime DESC
                        LIMIT       ?
                    ) newest_sessions
                    ON      newest_sessions.sessionID = all_sessions.sessionID
                    WHERE   all_sessions.userID = ?
                        AND newest_sessions.sessionID IS NULL";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $user->userID,
                self::USER_SESSION_LIMIT,
                $user->userID,
            ]);
            foreach ($statement->fetchAll(\PDO::FETCH_COLUMN) as $sessionID) {
                $this->deleteUserSession($sessionID);
            }

            // ... and reload the session with the updated information.
            $hasSession = $this->getExistingSession($this->sessionID);

            if (!$hasSession) {
                throw new \LogicException('Unreachable');
            }

            // Replace the session-lived cookie by a long-lived cookie.
            HeaderUtil::setCookie(
                'user_session',
                $this->getCookieValue(),
                TIME_NOW + (self::USER_SESSION_LIFETIME + (7 * 86400))
            );

            foreach ($saveVars as $key => $value) {
                $this->register($key, $value);
            }
        }
    }

    /**
     * Checks whether the user needs to authenticate themselves once again
     * to access a security critical area.
     *
     * If `true` is returned you should perform a redirect to `ReAuthenticationForm`,
     * otherwise the user is sufficiently authenticated and may proceed.
     *
     * @throws \BadMethodCallException If the current user is a guest.
     * @since 5.4
     */
    public function needsReauthentication(): bool
    {
        if (!$this->getUser()->userID) {
            throw new \BadMethodCallException('The current user is a guest.');
        }

        // Reauthentication for third party authentication is not supported.
        if ($this->getUser()->authData) {
            return false;
        }

        $data = $this->getVar(self::REAUTHENTICATION_KEY);

        // Request a new authentication if no stored information is available.
        if (!$data) {
            return true;
        }

        $lastAuthentication = $data['lastAuthentication'];
        $lastCheck = $data['lastCheck'];

        // Request a new authentication if the hard limit since the last authentication
        // is exceeded.
        if ($lastAuthentication < (TIME_NOW - self::REAUTHENTICATION_HARD_LIMIT)) {
            return true;
        }

        $softLimit = self::REAUTHENTICATION_SOFT_LIMIT;
        if ($this->isACP) {
            $softLimit = self::REAUTHENTICATION_SOFT_LIMIT_ACP;

            // If both the debug mode and the developer tools are enabled the
            // reauthentication soft limit within the ACP matches the hard limit.
            //
            // This allows for a continous access to the ACP and specifically the
            // developer tools within a single workday without needing to re-login
            // just because one spent 15 minutes within the IDE.
            if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
                $softLimit = self::REAUTHENTICATION_HARD_LIMIT;
            }
        }

        // Request a new authentication if the soft limit since the last authentication
        // is exceeded ...
        if ($lastAuthentication < (TIME_NOW - $softLimit)) {
            // ... and the grace period since the last check is also exceeded.
            if ($_POST === [] || $lastCheck < (TIME_NOW - self::REAUTHENTICATION_GRACE_PERIOD)) {
                return true;
            }
        }

        // If we reach this point we determined that a new authentication is not necessary.
        \assert(
            ($lastAuthentication >= TIME_NOW - $softLimit)
                || ($lastAuthentication >= TIME_NOW - self::REAUTHENTICATION_HARD_LIMIT
                    && $lastCheck >= TIME_NOW - self::REAUTHENTICATION_GRACE_PERIOD)
        );

        // Update the lastCheck timestamp to make sure that the grace period works properly.
        //
        // The grace period allows the user to complete their action if the soft limit
        // expires between loading a form and actually submitting that form, provided that
        // the user does not take longer than the grace period to fill in the form.
        $data['lastCheck'] = TIME_NOW;
        $this->register(self::REAUTHENTICATION_KEY, $data);

        return false;
    }

    /**
     * Registers that the user performed reauthentication successfully.
     *
     * This method should be considered to be semi-public and is intended to be used
     * by `ReAuthenticationForm` only.
     *
     * @throws \BadMethodCallException If the current user is a guest.
     * @see SessionHandler::needsReauthentication()
     * @since 5.4
     */
    public function registerReauthentication(): void
    {
        if (!$this->getUser()->userID) {
            throw new \BadMethodCallException('The current user is a guest.');
        }

        $this->register(self::REAUTHENTICATION_KEY, [
            'lastAuthentication' => TIME_NOW,
            'lastCheck' => TIME_NOW,
        ]);
    }

    /**
     * Clears that the user performed reauthentication successfully.
     *
     * After this method is called `needsReauthentication()` will return true until
     * `registerReauthentication()` is called again.
     *
     * @throws \BadMethodCallException If the current user is a guest.
     * @see SessionHandler::needsReauthentication()
     * @see SessionHandler::registerReauthentication()
     * @since 5.4
     */
    public function clearReauthentication(): void
    {
        if (!$this->getUser()->userID) {
            throw new \BadMethodCallException('The current user is a guest.');
        }

        $this->unregister(self::REAUTHENTICATION_KEY);
    }

    /**
     * Updates user session on shutdown.
     */
    public function update(): void
    {
        if ($this->doNotUpdate) {
            return;
        }

        if ($this->variablesChanged) {
            $sql = "UPDATE  wcf1_user_session
                    SET     sessionVariables = ?
                    WHERE   sessionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                \serialize($this->variables),
                $this->sessionID,
            ]);

            // Reset the flag, because the variables are no longer dirty.
            $this->variablesChanged = false;
        }

        $data = [
            'ipAddress' => UserUtil::getIpAddress(),
            'userAgent' => $this->userAgent,
            'requestURI' => $this->requestURI,
            'requestMethod' => $this->requestMethod,
            'lastActivityTime' => TIME_NOW,
            'sessionID' => $this->sessionID,
        ];
        if (!\class_exists('wcf\system\CLIWCF', false) && !$this->disableTracking) {
            $pageLocations = PageLocationManager::getInstance()->getLocations();
            if (isset($pageLocations[0])) {
                $data['pageID'] = $pageLocations[0]['pageID'];
                $data['pageObjectID'] = ($pageLocations[0]['pageObjectID'] ?: null);
                $data['parentPageID'] = null;
                $data['parentPageObjectID'] = null;

                for ($i = 1, $length = \count($pageLocations); $i < $length; $i++) {
                    if (!empty($pageLocations[$i]['useAsParentLocation'])) {
                        $data['parentPageID'] = $pageLocations[$i]['pageID'];
                        $data['parentPageObjectID'] = ($pageLocations[$i]['pageObjectID'] ?: null);
                        break;
                    }
                }
            }
        }

        if ($this->legacySession !== null) {
            $sessionEditor = new SessionEditor($this->legacySession);
            $sessionEditor->update($data);
        }
    }

    /**
     * @deprecated 5.4 - This method is a noop. The lastActivityTime is always updated immediately after loading.
     */
    public function keepAlive()
    {
    }

    /**
     * Deletes this session and its related data.
     */
    public function delete(): void
    {
        // clear storage
        if ($this->user->userID) {
            self::resetSessions([$this->user->userID]);

            // update last activity time
            $editor = new UserEditor($this->user);
            $editor->update(['lastActivityTime' => TIME_NOW]);
        }

        $this->deleteUserSession($this->sessionID);
    }

    /**
     * Prunes expired sessions.
     */
    public function prune(): void
    {
        $sql = "DELETE FROM wcf1_user_session
                WHERE       (lastActivityTime < ? AND userID IS NULL)
                         OR (lastActivityTime < ? AND userID IS NOT NULL)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - self::GUEST_SESSION_LIFETIME,
            TIME_NOW - self::USER_SESSION_LIFETIME,
        ]);

        // Legacy sessions live 120 minutes, they will be re-created on demand.
        $sql = "DELETE FROM wcf1_session
                WHERE       lastActivityTime < ?";
        $statement = WCF::getDB()->prepare($sql);
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
    public function deleteIfNew(): void
    {
        if ($this->isFirstVisit() && !$this->getUser()->userID) {
            $this->delete();
        }
    }

    /**
     * Returns currently active language id.
     *
     * @return  int
     */
    public function getLanguageID()
    {
        return $this->languageID;
    }

    /**
     * Sets the currently active language id.
     *
     * @param int $languageID
     */
    public function setLanguageID($languageID)
    {
        $this->languageID = $languageID;
        $this->register('languageID', $this->languageID);
    }

    /**
     * Resets session-specific storage data.
     *
     * @param int[] $userIDs
     * @deprecated 6.1 see https://github.com/WoltLab/WCF/pull/3767
     */
    public static function resetSessions(array $userIDs = [])
    {
        if (!empty($userIDs)) {
            UserStorageHandler::getInstance()->reset($userIDs, 'groupIDs');
            UserStorageHandler::getInstance()->reset($userIDs, 'languageIDs');
        } else {
            UserStorageHandler::getInstance()->resetAll('groupIDs');
            UserStorageHandler::getInstance()->resetAll('languageIDs');
        }
    }

    /**
     * Returns true if this is a new session.
     */
    public function isFirstVisit(): bool
    {
        return $this->firstVisit;
    }

    /**
     * Returns all sessions for a specific user.
     *
     * @return      Session[]
     * @throws      \InvalidArgumentException if the given user is a guest.
     * @since       5.4
     */
    public function getUserSessions(User $user): array
    {
        if (!$user->userID) {
            throw new \InvalidArgumentException("The given user is a guest.");
        }

        $sql = "SELECT  *
                FROM    wcf1_user_session
                WHERE   userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$user->userID]);

        $sessions = [];
        while ($row = $statement->fetchArray()) {
            $sessions[] = new Session($row);
        }

        return $sessions;
    }

    /**
     * Deletes the sessions for a specific user, except the session with the given session id.
     *
     * If the given session id is `null` or unknown, all sessions of the user will be deleted.
     *
     * @throws      \InvalidArgumentException if the given user is a guest.
     * @since       5.4
     */
    public function deleteUserSessionsExcept(User $user, ?string $sessionID = null): void
    {
        if (!$user->userID) {
            throw new \InvalidArgumentException("The given user is a guest.");
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('userID = ?', [$user->userID]);

        if ($sessionID !== null) {
            $conditionBuilder->add('sessionID <> ?', [$sessionID]);
        }

        $sql = "DELETE FROM wcf1_user_session
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        // Delete legacy session.
        $sql = "DELETE FROM wcf1_session
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    /**
     * Deletes a session with the given session ID.
     *
     * @since       5.4
     */
    public function deleteUserSession(string $sessionID): void
    {
        $sql = "DELETE FROM wcf1_user_session
                WHERE       sessionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$sessionID]);

        // Delete legacy session.
        $sql = "DELETE FROM wcf1_session
                WHERE       sessionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$sessionID]);
    }

    /**
     * Returns the session variables.
     *
     * @since 6.1
     */
    public function getVariables(): array
    {
        $scope = $this->isACP ? 'acp' : 'frontend';

        return $this->variables[$scope] ?? [];
    }
}
