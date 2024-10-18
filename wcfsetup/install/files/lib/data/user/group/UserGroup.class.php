<?php

namespace wcf\data\user\group;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\data\user\User;
use wcf\system\cache\builder\UserGroupCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a user group.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $groupID        unique id of the user group
 * @property-read   string $groupName      name of the user group or name of language
 *                item which contains the name
 * @property-read   string $groupDescription   description of the user group or name of
 *                language item which contains the description
 * @property-read   int $groupType      identifier of the type of user group
 * @property-read   int $priority       priority of the user group used to determine
 *                member's user rank and online marking
 * @property-read   string $userOnlineMarking  HTML code used to print the formatted name of
 *                a user group member
 * @property-read   int $showOnTeamPage     is `1` if the user group and its members
 *                should be shown on the team page, otherwise `0`
 * @property-read       int $allowMention           is `1` if the user group can be mentioned in messages,
 *                      otherwise `0`
 * @property-read       int $requireMultifactor           is `1` if group members need to set up multi-factor
 *                      authentcation, otherwise `0`
 */
class UserGroup extends DatabaseObject implements ITitledObject
{
    /**
     * group type everyone user group
     * @var int
     */
    const EVERYONE = 1;

    /**
     * group type guests user group
     * @var int
     */
    const GUESTS = 2;

    /**
     * group type registered users user group
     * @var int
     */
    const USERS = 3;

    /**
     * group type of other user groups
     * @var int
     */
    const OTHER = 4;

    /**
     * the owner group is always an administrator group
     * @var int
     */
    const OWNER = 9;

    /**
     * group cache
     * @var UserGroup[]
     */
    protected static $cache;

    /**
     * list of accessible groups for active user
     * @var int[]
     */
    protected static $accessibleGroups;

    /**
     * @var UserGroup|null
     */
    protected static $ownerGroup = false;

    /**
     * group options of this group
     * @var mixed[][]
     */
    protected $groupOptions;

    /**
     * Returns group ids by given type.
     *
     * @param int[] $types
     * @return  int[]
     */
    public static function getGroupIDsByType(array $types)
    {
        self::getCache();

        $groupIDs = [];
        foreach ($types as $type) {
            if (isset(self::$cache['types'][$type])) {
                $groupIDs = \array_merge($groupIDs, self::$cache['types'][$type]);
            }
        }

        return \array_unique($groupIDs);
    }

    /**
     * Returns groups by given type. Returns all groups if no types given.
     *
     * @param int[] $types
     * @param int[] $invalidGroupTypes
     * @return  UserGroup[]
     */
    public static function getGroupsByType(array $types = [], array $invalidGroupTypes = [])
    {
        self::getCache();

        $groups = [];
        foreach (self::$cache['groups'] as $group) {
            if (
                (empty($types) || \in_array($group->groupType, $types)) && !\in_array(
                    $group->groupType,
                    $invalidGroupTypes
                )
            ) {
                $groups[$group->groupID] = $group;
            }
        }

        return $groups;
    }

    /**
     * Returns a sorted list of groups filtered by given type.
     *
     * @param int[] $types
     * @param int[] $invalidGroupTypes
     * @return  UserGroup[]
     * @since       5.3
     */
    public static function getSortedGroupsByType(array $types = [], array $invalidGroupTypes = [])
    {
        $userGroups = self::getGroupsByType($types, $invalidGroupTypes);

        self::sortGroups($userGroups);

        return $userGroups;
    }

    /**
     * Returns unique group by given type. Only works for the default user groups.
     *
     * @param int $type
     * @return  UserGroup
     * @throws  SystemException
     */
    public static function getGroupByType($type)
    {
        if ($type != self::EVERYONE && $type != self::GUESTS && $type != self::USERS && $type != self::OWNER) {
            throw new SystemException('invalid value for type argument');
        }

        $groups = self::getGroupsByType([$type]);

        return \array_shift($groups);
    }

    /**
     * Returns the user group with the given id or null if no such user group
     * exists.
     *
     * @param int $groupID
     * @return  UserGroup|null
     */
    public static function getGroupByID($groupID)
    {
        self::getCache();

        return self::$cache['groups'][$groupID] ?? null;
    }

    /**
     * Returns a list of groups by group id.
     *
     * @param int[] $groupIDs list of group ids
     * @return      UserGroup[]
     */
    public static function getGroupsByIDs(array $groupIDs)
    {
        $groups = [];
        foreach ($groupIDs as $groupID) {
            $group = self::getGroupByID($groupID);
            if ($group !== null) {
                $groups[$groupID] = $group;
            }
        }

        return $groups;
    }

    /**
     * Returns true if the given user is member of the group. If no user is
     * given, the active user is used.
     *
     * @param User $user user object or current user if null
     * @return  bool
     */
    public function isMember(?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        if (\in_array($this->groupID, $user->getGroupIDs())) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if this is the 'Everyone' group.
     *
     * @return  bool
     * @since   3.0
     */
    public function isEveryone()
    {
        return $this->groupType == self::EVERYONE;
    }

    /**
     * Returns true if this is the 'Users' group.
     *
     * @return      bool
     * @since       3.1
     */
    public function isUsers()
    {
        return $this->groupType == self::USERS;
    }

    /**
     * Returns true if this is the 'Owner' group.
     *
     * @return      bool
     * @since       5.2
     */
    public function isOwner()
    {
        return $this->groupType == self::OWNER;
    }

    /**
     * Returns `true` if the active user can copy this user group.
     *
     * @return      bool
     * @since       5.3
     */
    public function canCopy()
    {
        return WCF::getSession()->getPermission('admin.user.canAddGroup') && $this->isAccessible();
    }

    /**
     * Returns true if the given groups are accessible for the active user.
     *
     * @param array $groupIDs
     * @return  bool
     */
    public static function isAccessibleGroup(array $groupIDs = [])
    {
        if (self::$accessibleGroups === null) {
            self::$accessibleGroups = \explode(
                ',',
                WCF::getSession()->getPermission('admin.user.accessibleGroups') ?: ''
            );
        }

        if (empty($groupIDs)) {
            return false;
        }

        foreach ($groupIDs as $groupID) {
            if (!\in_array($groupID, self::$accessibleGroups)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a list of accessible groups.
     *
     * @param int[] $groupTypes
     * @param int[] $invalidGroupTypes
     * @return  UserGroup[]
     */
    public static function getAccessibleGroups(array $groupTypes = [], array $invalidGroupTypes = [])
    {
        $groups = self::getGroupsByType($groupTypes, $invalidGroupTypes);

        foreach ($groups as $key => $value) {
            if (!self::isAccessibleGroup([$key])) {
                unset($groups[$key]);
            }
        }

        return $groups;
    }

    /**
     * Returns a sorted list of accessible groups.
     *
     * @param int[] $groupTypes
     * @param int[] $invalidGroupTypes
     * @return  UserGroup[]
     * @since       5.2
     */
    public static function getSortedAccessibleGroups(array $groupTypes = [], array $invalidGroupTypes = [])
    {
        $userGroups = self::getAccessibleGroups($groupTypes, $invalidGroupTypes);

        self::sortGroups($userGroups);

        return $userGroups;
    }

    /**
     * Returns true if the current group is an admin-group, which requires it to fulfill
     * one of these conditions:
     *  a) The WCFSetup is running and the group id is 4.
     *  b) This is the 'Owner' group.
     *  c) The group can access all groups (the 'Owner' group does not count).
     *
     * @return  bool
     */
    public function isAdminGroup()
    {
        // WCFSetup
        if (!PACKAGE_ID && $this->groupID == 4) {
            return true;
        }

        if ($this->groupType === self::OWNER) {
            return true;
        }

        $groupIDs = \array_keys(self::getGroupsByType([], [self::OWNER]));
        $accessibleGroupIDs = \explode(',', (string)$this->getGroupOption('admin.user.accessibleGroups'));

        // no differences -> all groups are included
        return \count(\array_diff($groupIDs, $accessibleGroupIDs)) == 0 ? true : false;
    }

    /**
     * Returns true if the current group is a moderator-group.
     *
     * @return  bool
     */
    public function isModGroup()
    {
        // workaround for WCF-Setup
        if (!PACKAGE_ID && ($this->groupID == 5 || $this->groupID == 4)) {
            return true;
        }

        return $this->getGroupOption('mod.general.canUseModeration');
    }

    /**
     * Loads the group cache.
     */
    protected static function getCache()
    {
        if (self::$cache === null) {
            self::$cache = UserGroupCacheBuilder::getInstance()->getData();
        }
    }

    /**
     * Returns true if this group is accessible for the active user.
     *
     * @return  bool
     */
    public function isAccessible()
    {
        return self::isAccessibleGroup([$this->groupID]);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Returns the name of this user group.
     *
     * @return  string
     */
    public function getName()
    {
        return WCF::getLanguage()->get($this->groupName);
    }

    /**
     * Sets the name of this user group.
     *
     * This method is only needed to set the current name if it has been changed
     * in the same request.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->data['groupName'] = $name;
    }

    /**
     * Returns true if current user may delete this group.
     *
     * @return  bool
     */
    public function isDeletable()
    {
        // insufficient permissions
        if (!WCF::getSession()->getPermission('admin.user.canDeleteGroup')) {
            return false;
        }

        // cannot delete own groups
        if ($this->isMember()) {
            return false;
        }

        // user cannot delete this group
        if (!$this->isAccessible()) {
            return false;
        }

        // cannot delete static groups
        if ($this->groupType == self::EVERYONE || $this->groupType == self::GUESTS || $this->groupType == self::USERS || $this->groupType == self::OWNER) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if current user may edit this group.
     *
     * @return  bool
     */
    public function isEditable()
    {
        // insufficient permissions
        if (!WCF::getSession()->getPermission('admin.user.canEditGroup')) {
            return false;
        }

        // user cannot edit this group
        if (!$this->isAccessible()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the value of the group option with the given name.
     *
     * @param string $name
     * @return  mixed
     */
    public function getGroupOption($name)
    {
        if ($this->groupOptions === null) {
            // get all options and filter options with low priority
            $this->groupOptions = [];

            $sql = "SELECT  optionName, optionID
                    FROM    wcf1_user_group_option";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
            $groupOptionIDs = $statement->fetchMap('optionName', 'optionID');

            if (!empty($groupOptionIDs)) {
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add("option_value.groupID = ?", [$this->groupID]);
                $conditions->add("option_value.optionID IN (?)", [$groupOptionIDs]);

                $sql = "SELECT      group_option.optionName, option_value.optionValue
                        FROM        wcf1_user_group_option_value option_value
                        LEFT JOIN   wcf1_user_group_option group_option
                        ON          group_option.optionID = option_value.optionID
                        " . $conditions;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
                $this->groupOptions = $statement->fetchMap('optionName', 'optionValue');
            }
        }

        if (isset($this->groupOptions[$name])) {
            return $this->groupOptions[$name];
        }
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->groupName);
    }

    /**
     * Returns the user group description in the active user's language.
     *
     * @return  string
     * @since   5.2
     */
    public function getDescription()
    {
        return WCF::getLanguage()->get($this->groupDescription ?: '');
    }

    /**
     * The `Everyone`, `Guests` and `Users` group can never be mentioned.
     *
     * @return bool
     * @since 5.2
     */
    public function isUnmentionableGroup()
    {
        return \in_array($this->groupType, [self::EVERYONE, self::GUESTS, self::USERS]);
    }

    /**
     * Returns true if this group can be mentioned, is always false for the
     * `Everyone`, `Guests` and `Users` group.
     *
     * @return bool
     * @since 5.2
     */
    public function canBeMentioned()
    {
        if ($this->isUnmentionableGroup()) {
            return false;
        }

        return !!$this->allowMention;
    }

    /**
     * @return UserGroup[]
     * @since 5.2
     */
    public static function getMentionableGroups()
    {
        if (!WCF::getSession()->getPermission('user.message.canMentionGroups')) {
            return [];
        }

        self::getCache();

        $groups = [];
        /** @var UserGroup $group */
        foreach (self::$cache['groups'] as $group) {
            if ($group->canBeMentioned()) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * @return UserGroup[]
     * @since 5.2
     */
    public static function getAllGroups()
    {
        self::getCache();

        return self::$cache['groups'];
    }

    /**
     * Returns the list of irrevocable permissions of the owner group.
     *
     * @return string[]
     * @since 5.2
     */
    public static function getOwnerPermissions()
    {
        return [
            'admin.configuration.canEditOption',
            'admin.configuration.canManageApplication',
            'admin.configuration.package.canInstallPackage',
            'admin.configuration.package.canUninstallPackage',
            'admin.configuration.package.canUpdatePackage',
            'admin.general.canUseAcp',
            'admin.general.canViewPageDuringOfflineMode',
            'admin.user.canEditGroup',
            'admin.user.canEditUser',
            'admin.user.canSearchUser',
        ];
    }

    /**
     * Returns the owner group's id unless no group was promoted yet due to backwards compatibility.
     *
     * @return int|null
     * @since 5.2
     */
    public static function getOwnerGroupID()
    {
        if (self::$ownerGroup === false) {
            self::$ownerGroup = self::getGroupByType(self::OWNER);
        }

        return self::$ownerGroup ? self::$ownerGroup->groupID : null;
    }

    /**
     * Sorts the given user groups alphabetically.
     *
     * @param UserGroup[] $userGroups
     * @since       5.3
     */
    public static function sortGroups(array &$userGroups)
    {
        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $userGroups,
            static fn (self $groupA, self $groupB) => $collator->compare($groupA->getName(), $groupB->getName())
        );
    }
}
