<?php

namespace wcf\data\user;

use wcf\data\DatabaseObject;
use wcf\data\IPopoverObject;
use wcf\data\IUserContent;
use wcf\data\language\Language;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\password\algorithm\DoubleBcrypt;
use wcf\system\user\authentication\password\PasswordAlgorithmManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\UserUtil;

/**
 * Represents a user.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $userID             unique id of the user
 * @property-read   string $username           name of the user
 * @property-read   string $email              email address of the user
 * @property-read   string $password           double salted hash of the user's password
 * @property-read   string $accessToken            token used for access authentication, for example used by feed pages
 * @property-read   int $languageID         id of the interface language used by the user
 * @property-read   int $registrationDate       timestamp at which the user has registered/has been created
 * @property-read   int $styleID            id of the style used by the user
 * @property-read   int $banned             is `1` if the user is banned, otherwise `0`
 * @property-read   string $banReason          reason why the user is banned
 * @property-read   int $banExpires         timestamp at which the banned user is automatically unbanned
 * @property-read   int $activationCode         flag which determines, whether the user is activated (for legacy reasons an random integer, if the user is *not* activated)
 * @property-read   string $emailConfirmed         code sent to the user's email address used for account activation or null if the email is confirmed
 * @property-read   int $lastLostPasswordRequestTime    timestamp at which the user has reported that they lost their password or 0 if password has not been reported as lost
 * @property-read   string $lostPasswordKey        code used for authenticating setting new password after password loss or empty if password has not been reported as lost
 * @property-read   int $lastUsernameChange     timestamp at which the user changed their name the last time or 0 if username has not been changed
 * @property-read   string $newEmail           new email address of the user that has to be manually confirmed or empty if no new email address has been set
 * @property-read   string $oldUsername            previous name of the user or empty if they have had no previous name
 * @property-read   int $quitStarted            timestamp at which the user terminated their account
 * @property-read   int $reactivationCode       code used for authenticating setting new email address or empty if no new email address has been set
 * @property-read   string $registrationIpAddress      ip address of the user at the time of registration or empty if user has been created manually or if no ip address are logged
 * @property-read   int|null $avatarID           id of the user's avatar or null if they have no avatar
 * @property-read   int $disableAvatar          is `1` if the user's avatar has been disabled, otherwise `0`
 * @property-read   string $disableAvatarReason        reason why the user's avatar is disabled
 * @property-read   int $disableAvatarExpires       timestamp at which the user's avatar will automatically be enabled again
 * @property-read   string $signature          text of the user's signature
 * @property-read   int $signatureEnableHtml        is `1` if HTML will rendered in the user's signature, otherwise `0`
 * @property-read   int $disableSignature       is `1` if the user's signature has been disabled, otherwise `0`
 * @property-read   string $disableSignatureReason     reason why the user's signature is disabled
 * @property-read   int $disableSignatureExpires    timestamp at which the user's signature will automatically be enabled again
 * @property-read   int $lastActivityTime       timestamp of the user's last activity
 * @property-read   int $profileHits            number of times the user's profile has been visited
 * @property-read   int|null $rankID             id of the user's rank or null if they have no rank
 * @property-read   string $userTitle          custom user title used instead of rank title or empty if user has no custom title
 * @property-read   int|null $userOnlineGroupID      id of the user group whose online marking is used when printing the user's formatted name or null if no special marking is used
 * @property-read   int $activityPoints         total number of the user's activity points
 * @property-read   string $notificationMailToken      token used for authenticating requests by the user to disable notification emails
 * @property-read   string $authData           data of the third party used for authentication
 * @property-read   int $likesReceived          cumulative result of likes (counting +1) the user's contents have received
 * @property-read       string $coverPhotoHash                 hash of the user's cover photo
 * @property-read   string $coverPhotoExtension        extension of the user's cover photo file
 * @property-read int $coverPhotoHasWebP is `1` if a webp variant of the cover photo and its thumbnail exists, otherwise `0`
 * @property-read       int $disableCoverPhoto              is `1` if the user's cover photo has been disabled, otherwise `0`
 * @property-read   string $disableCoverPhotoReason    reason why the user's cover photo is disabled
 * @property-read   int $disableCoverPhotoExpires   timestamp at which the user's cover photo will automatically be enabled again
 * @property-read   int $articles           number of articles written by the user
 * @property-read       string $blacklistMatches               JSON string of an array with all matches in the blacklist, otherwise an empty string
 * @property-read       int $multifactorActive              is `1` if the use has enabled a second factor, otherwise `0`
 * @property-read       int $trophyPoints              total number of user's trophies in active categories
 */
final class User extends DatabaseObject implements IPopoverObject, IRouteController, IUserContent
{
    /**
     * list of group ids
     * @var int[]
     */
    protected $groupIDs;

    /**
     * true, if user has access to the ACP
     * @var bool
     */
    protected $hasAdministrativePermissions;

    /**
     * list of language ids
     * @var int[]
     */
    protected $languageIDs;

    /**
     * date time zone object
     * @var \DateTimeZone
     */
    protected $timezoneObj;

    /**
     * list of user options
     * @var UserOption[]
     */
    protected static $userOptions;

    const REGISTER_ACTIVATION_NONE = 0;

    const REGISTER_ACTIVATION_USER = 1;

    const REGISTER_ACTIVATION_ADMIN = 2;

    const REGISTER_ACTIVATION_USER_AND_ADMIN = self::REGISTER_ACTIVATION_USER | self::REGISTER_ACTIVATION_ADMIN;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * @inheritDoc
     */
    public function __construct($id, $row = null, ?DatabaseObject $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT      user_option_value.*, user_table.*
                    FROM        wcf" . WCF_N . "_user user_table
                    LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
                    ON          user_option_value.userID = user_table.userID
                    WHERE       user_table.userID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$id]);
            $row = $statement->fetchArray();

            // enforce data type 'array'
            if ($row === false) {
                $row = [];
            }
        } elseif ($object !== null) {
            $row = $object->data;
        }

        $this->handleData($row);
    }

    /**
     * Returns true if the given password is the correct password for this user.
     *
     * @param string $password
     * @return  bool        password correct
     */
    public function checkPassword(
        #[\SensitiveParameter]
        $password
    ) {
        $isValid = false;

        $manager = PasswordAlgorithmManager::getInstance();

        // Compatibility for WoltLab Suite < 5.4.
        if (DoubleBcrypt::isLegacyDoubleBcrypt($this->password)) {
            $algorithmName = 'DoubleBcrypt';
            $hash = $this->password;
        } else {
            [$algorithmName, $hash] = \explode(':', $this->password, 2);
        }

        $algorithm = $manager->getAlgorithmFromName($algorithmName);

        $isValid = $algorithm->verify($password, $hash);

        if (!$isValid) {
            return false;
        }

        $defaultAlgorithm = $manager->getDefaultAlgorithm();
        if (\get_class($algorithm) !== \get_class($defaultAlgorithm) || $algorithm->needsRehash($hash)) {
            $userEditor = new UserEditor($this);
            $userEditor->update([
                'password' => $password,
            ]);
        }

        // $isValid is always true at this point. However we intentionally use a variable
        // that defaults to false to prevent accidents during refactoring.
        \assert($isValid);

        return $isValid;
    }

    /**
     * @deprecated 5.4 - This method always returns false, as user sessions are long-lived now.
     */
    public function checkCookiePassword($passwordHash)
    {
        return false;
    }

    /**
     * Returns an array with all the groups in which the actual user is a member.
     *
     * @param bool $skipCache
     * @return  int[]
     */
    public function getGroupIDs($skipCache = false)
    {
        if ($this->groupIDs === null || $skipCache) {
            if (!$this->userID) {
                // user is a guest, use default guest group
                $this->groupIDs = UserGroup::getGroupIDsByType([UserGroup::GUESTS, UserGroup::EVERYONE]);
            } else {
                // get group ids
                $data = UserStorageHandler::getInstance()->getField('groupIDs', $this->userID);

                // cache does not exist or is outdated
                if ($data === null || $skipCache) {
                    $sql = "SELECT  groupID
                            FROM    wcf" . WCF_N . "_user_to_group
                            WHERE   userID = ?";
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute([$this->userID]);
                    $this->groupIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                    // update storage data
                    if (!$skipCache) {
                        UserStorageHandler::getInstance()->update(
                            $this->userID,
                            'groupIDs',
                            \serialize($this->groupIDs)
                        );
                    }
                } else {
                    $this->groupIDs = \unserialize($data);
                }
            }

            \sort($this->groupIDs, \SORT_NUMERIC);
        }

        return $this->groupIDs;
    }

    /**
     * Returns a list of language ids for this user.
     *
     * @return  int[]
     */
    public function getLanguageIDs()
    {
        if ($this->languageIDs === null) {
            $this->languageIDs = [];

            if ($this->userID) {
                // get language ids
                $data = UserStorageHandler::getInstance()->getField('languageIDs', $this->userID);

                // cache does not exist or is outdated
                if ($data === null) {
                    $sql = "SELECT  languageID
                            FROM    wcf" . WCF_N . "_user_to_language
                            WHERE   userID = ?";
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute([$this->userID]);
                    $this->languageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                    // update storage data
                    UserStorageHandler::getInstance()->update(
                        $this->userID,
                        'languageIDs',
                        \serialize($this->languageIDs)
                    );
                } else {
                    $this->languageIDs = \unserialize($data);
                }
            } else {
                $this->languageIDs = LanguageFactory::getInstance()->getContentLanguageIDs();
            }
        }

        return $this->languageIDs;
    }

    /**
     * Returns the value of the user option with the given name.
     *
     * @param string $name user option name
     * @param bool $filterDisabled suppress values for disabled options
     * @return  mixed               user option value
     */
    public function getUserOption($name, $filterDisabled = false)
    {
        $optionID = self::getUserOptionID($name);
        if ($optionID === null) {
            return;
        } elseif ($filterDisabled && self::$userOptions[$name]->isDisabled) {
            return;
        }

        return $this->data['userOption' . $optionID] ?? null;
    }

    /**
     * Fetches all user options from cache.
     */
    protected static function getUserOptionCache()
    {
        self::$userOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
    }

    /**
     * Returns the id of a user option.
     *
     * @param string $name
     * @return  int|null
     */
    public static function getUserOptionID($name)
    {
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
    public function __get($name)
    {
        return $this->data[$name] ?? $this->getUserOption($name);
    }

    /**
     * Returns the user with the given username.
     *
     * @param string $username
     * @return  User
     */
    public static function getUserByUsername($username)
    {
        $sql = "SELECT      user_option_value.*, user_table.*
                FROM        wcf" . WCF_N . "_user user_table
                LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
                ON          user_option_value.userID = user_table.userID
                WHERE       user_table.username = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$username]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Returns the user with the given email.
     *
     * @param string $email
     * @return  User
     */
    public static function getUserByEmail($email)
    {
        $sql = "SELECT      user_option_value.*, user_table.*
                FROM        wcf" . WCF_N . "_user user_table
                LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
                ON          user_option_value.userID = user_table.userID
                WHERE       user_table.email = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$email]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Returns the user with the given authData.
     *
     * @param string $authData
     * @return  User
     */
    public static function getUserByAuthData($authData)
    {
        $sql = "SELECT      user_option_value.*, user_table.*
                FROM        wcf" . WCF_N . "_user user_table
                LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
                ON          user_option_value.userID = user_table.userID
                WHERE       user_table.authData = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$authData]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Returns 3rd party auth provider name.
     *
     * @return  string
     * @since       5.2
     */
    public function getAuthProvider()
    {
        if (!$this->authData) {
            return '';
        }

        return \mb_substr($this->authData, 0, \mb_strpos($this->authData, ':'));
    }

    /**
     * Returns true if this user is marked.
     *
     * @return  bool
     */
    public function isMarked()
    {
        $markedUsers = WCF::getSession()->getVar('markedUsers');
        if ($markedUsers !== null) {
            if (\in_array($this->userID, $markedUsers)) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Returns true if the email is confirmed.
     *
     * @return  bool
     * @since       5.3
     */
    public function isEmailConfirmed()
    {
        return $this->emailConfirmed === null;
    }

    /**
     * Returns the time zone of this user.
     *
     * @return  \DateTimeZone
     */
    public function getTimeZone()
    {
        if ($this->timezoneObj === null) {
            if ($this->timezone) {
                $this->timezoneObj = new \DateTimeZone($this->timezone);
            } else {
                $this->timezoneObj = new \DateTimeZone(TIMEZONE);
            }
        }

        return $this->timezoneObj;
    }

    /**
     * Returns a list of users.
     *
     * @param array $userIDs
     * @return  User[]
     */
    public static function getUsers(array $userIDs)
    {
        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();

        return $userList->getObjects();
    }

    /**
     * Returns username.
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableAlias()
    {
        return 'user_table';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->username ?: '';
    }

    /**
     * Returns the language of this user.
     *
     * @return  Language
     */
    public function getLanguage()
    {
        $language = LanguageFactory::getInstance()->getLanguage($this->languageID);
        if ($language === null) {
            $language = LanguageFactory::getInstance()->getLanguage(LanguageFactory::getInstance()->getDefaultLanguageID());
        }

        return $language;
    }

    /**
     * Returns true if the active user can edit this user.
     *
     * @return  bool
     */
    public function canEdit()
    {
        return WCF::getSession()->getPermission('admin.user.canEditUser') && UserGroup::isAccessibleGroup($this->getGroupIDs());
    }

    /**
     * Returns true, if this user has access to the ACP.
     *
     * @return  bool
     */
    public function hasAdministrativeAccess()
    {
        if ($this->hasAdministrativePermissions === null) {
            $this->hasAdministrativePermissions = false;

            if ($this->userID) {
                foreach (UserGroup::getGroupsByIDs($this->getGroupIDs()) as $group) {
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
     * Returns true, if this user is a member of the owner group.
     *
     * @return bool
     * @since 5.2
     */
    public function hasOwnerAccess()
    {
        foreach (UserGroup::getGroupsByIDs($this->getGroupIDs()) as $group) {
            if ($group->isOwner()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getTime()
    {
        return $this->registrationDate;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('User', [
            'application' => 'wcf',
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * Returns the registration ip address, attempts to convert to IPv4.
     *
     * @return      string
     */
    public function getRegistrationIpAddress()
    {
        if ($this->registrationIpAddress) {
            return UserUtil::convertIPv6To4($this->registrationIpAddress);
        }

        return '';
    }

    /**
     * Returns true, if this user can purchase paid subscriptions.
     *
     * @return      bool
     */
    public function canPurchasePaidSubscriptions()
    {
        return WCF::getUser()->userID && !$this->pendingActivation();
    }

    /**
     * Returns the list of fields that had matches in the blacklist. An empty list is
     * returned if the user has been approved, regardless of any known matches.
     *
     * @return string[]
     * @since 5.2
     */
    public function getBlacklistMatches()
    {
        if ($this->pendingActivation() && $this->blacklistMatches) {
            $matches = JSON::decode($this->blacklistMatches);
            if (\is_array($matches)) {
                return $matches;
            }
        }

        return [];
    }

    /**
     * Returns a human readable list of fields that have positive matches against the
     * blacklist. If you require the raw field names, please use `getBlacklistMatches()`
     * instead.
     *
     * @return string[]
     * @since 5.2
     */
    public function getBlacklistMatchesTitle()
    {
        return \array_map(static function ($field) {
            if ($field === 'ip') {
                $field = 'ipAddress';
            }

            return WCF::getLanguage()->get('wcf.user.' . $field);
        }, $this->getBlacklistMatches());
    }

    /**
     * Returns true if this user is not activated.
     *
     * @return  bool
     * @since       5.3
     */
    public function pendingActivation()
    {
        return $this->activationCode != 0;
    }

    /**
     * Returns true if this user requires activation by the user.
     *
     * @return  bool
     * @since       5.3
     */
    public function requiresEmailActivation()
    {
        return REGISTER_ACTIVATION_METHOD & self::REGISTER_ACTIVATION_USER && $this->pendingActivation() && !$this->isEmailConfirmed();
    }

    /**
     * Returns true if this user requires the activation by an admin.
     *
     * @return  bool
     * @since       5.3
     */
    public function requiresAdminActivation()
    {
        return REGISTER_ACTIVATION_METHOD & self::REGISTER_ACTIVATION_ADMIN && $this->pendingActivation();
    }

    /**
     * Returns true if this user can confirm the email themself.
     *
     * @return  bool
     * @since       5.3
     */
    public function canEmailConfirm()
    {
        return REGISTER_ACTIVATION_METHOD & self::REGISTER_ACTIVATION_USER && !$this->isEmailConfirmed();
    }

    /**
     * Returns true, if the user must confirm his email by themself.
     *
     * @return      bool
     * @since       5.3
     */
    public function mustSelfEmailConfirm()
    {
        return REGISTER_ACTIVATION_METHOD & self::REGISTER_ACTIVATION_USER;
    }

    /**
     * Returns true if the user is a member of a user group that requires
     * multi-factor authentication to be enabled.
     *
     * @since   5.4
     */
    public function requiresMultifactor(): bool
    {
        foreach (UserGroup::getGroupsByIDs($this->getGroupIDs()) as $group) {
            if ($group->requireMultifactor) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getPopoverLinkClass()
    {
        return 'userLink';
    }
}
