<?php
namespace wcf\data\user\group;
use wcf\data\user\User;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\UserGroupCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a user group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category	Community Framework
 */
class UserGroup extends DatabaseObject {
	/**
	 * group type everyone user group
	 * @var	integer
	 */
	const EVERYONE = 1;
	
	/**
	 * group type guests user group
	 * @var	integer
	 */
	const GUESTS = 2;
	
	/**
	 * group type registered users user group
	 * @var	integer
	 */
	const USERS = 3;
	
	/**
	 * group type of other user groups
	 * @var	integer
	 */
	const OTHER = 4;
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_group';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'groupID';
	
	/**
	 * group cache
	 * @var	array<\wcf\data\user\group\UserGroup>
	 */
	protected static $cache = null;
	
	/**
	 * list of accessible groups for active user
	 * @var	array<integer>
	 */
	protected static $accessibleGroups = null;
	
	/**
	 * group options of this group
	 * @var	array<array>
	 */
	protected $groupOptions = null;
	
	/**
	 * Returns group ids by given type.
	 * 
	 * @param	array<integer>		$types
	 * @return	array<integer>
	 */
	public static function getGroupIDsByType(array $types) {
		self::getCache();
		
		$groupIDs = array();
		foreach ($types as $type) {
			if (isset(self::$cache['types'][$type])) {
				$groupIDs = array_merge($groupIDs, self::$cache['types'][$type]);
			}
		}
		$groupIDs = array_unique($groupIDs);
		
		return $groupIDs;
	}
	
	/**
	 * Returns groups by given type. Returns all groups if no types given.
	 * 
	 * @param	array<integer>		$types
	 * @param	array<integer>		$invalidGroupTypes
	 * @return	array<\wcf\data\user\group\UserGroup>
	 */
	public static function getGroupsByType(array $types = array(), array $invalidGroupTypes = array()) {
		self::getCache();
		
		$groups = array();
		foreach (self::$cache['groups'] as $group) {
			if ((empty($types) || in_array($group->groupType, $types)) && !in_array($group->groupType, $invalidGroupTypes)) {
				$groups[$group->groupID] = $group;
			}
		}
		
		return $groups;
	}
	
	/**
	 * Returns unique group by given type. Only works for the default user groups.
	 * 
	 * @param	integer		$type
	 * @return	\wcf\data\user\group\UserGroup
	 */
	public static function getGroupByType($type) {
		if ($type != self::EVERYONE && $type != self::GUESTS && $type != self::USERS) {
			throw new SystemException('invalid value for type argument');
		}
		
		$groups = self::getGroupsByType(array($type));
		return array_shift($groups);
	}
	
	/**
	 * Returns the user group with the given id or null if no such user group
	 * exists.
	 * 
	 * @param	integer		$groupID
	 * @return	\wcf\data\user\group\UserGroup
	 */
	public static function getGroupByID($groupID) {
		self::getCache();
		
		if (isset(self::$cache['groups'][$groupID])) {
			return self::$cache['groups'][$groupID];
		}
		
		return null;
	}
	
	/**
	 * Returns true if the given user is member of the group. If no user is
	 * given, the active user is used.
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @return	boolean
	 */
	public function isMember(User $user = null) {
		if ($user === null) $user = WCF::getUser();
		
		if (in_array($this->groupID, $user->getGroupIDs())) return true;
		return false;
	}
	
	/**
	 * Returns true if this is the 'Everyone' group.
	 * 
	 * @return	boolean
	 */
	public function isEveryone() {
		return $this->groupType == self::EVERYONE;
	}
	
	/**
	 * Returns true if the given groups are accessible for the active user.
	 * 
	 * @param	array		$groupIDs
	 * @return	boolean
	 */
	public static function isAccessibleGroup(array $groupIDs = array()) {
		if (self::$accessibleGroups === null) {
			self::$accessibleGroups = explode(',', WCF::getSession()->getPermission('admin.user.accessibleGroups'));
		}
		
		if (empty($groupIDs)) return false;
		
		foreach ($groupIDs as $groupID) {
			if (!in_array($groupID, self::$accessibleGroups)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns a list of accessible groups.
	 * 
	 * @param	array<integer>		$groupTypes
	 * @param	array<integer>		$invalidGroupTypes
	 * @return	array<\wcf\data\user\group\UserGroup>
	 */
	public static function getAccessibleGroups(array $groupTypes = array(), array $invalidGroupTypes = array()) {
		$groups = self::getGroupsByType($groupTypes, $invalidGroupTypes);
		
		foreach ($groups as $key => $value) {
			if (!self::isAccessibleGroup(array($key))) {
				unset($groups[$key]);
			}
		}
		
		return $groups;
	}
	
	/**
	 * Returns true if the current group is an admin-group.
	 * Every group that may access EVERY group is an admin-group.
	 * 
	 * @return	boolean
	 */
	public function isAdminGroup() {
		// workaround for WCF-Setup
		if (!PACKAGE_ID && $this->groupID == 4) return true;
		
		$groupIDs = array_keys(self::getGroupsByType());
		$accessibleGroupIDs = explode(',', $this->getGroupOption('admin.user.accessibleGroups'));
		
		// no differences -> all groups are included
		return count(array_diff($groupIDs, $accessibleGroupIDs)) == 0 ? true : false;
	}
	
	/**
	 * Returns true if the current group is a moderator-group.
	 * 
	 * @reutn	boolean
	 */
	public function isModGroup() {
		// workaround for WCF-Setup
		if (!PACKAGE_ID && ($this->groupID == 5 || $this->groupID == 4)) return true;
		
		return $this->getGroupOption('mod.general.canUseModeration');
	}
	
	/**
	 * Loads the group cache.
	 */
	protected static function getCache() {
		if (self::$cache === null) {
			self::$cache = UserGroupCacheBuilder::getInstance()->getData();
		}
	}
	
	/**
	 * Returns true if this group is accessible for the active user.
	 * 
	 * @return	boolean
	 */
	public function isAccessible() {
		return self::isAccessibleGroup(array($this->groupID));
	}
	
	/**
	 * @see	\wcf\data\user\group\UserGroup::getName()
	 */
	public function __toString() {
		return $this->getName();
	}
	
	/**
	 * Returns the name of this user group.
	 * 
	 * @return	string
	 */
	public function getName() {
		return WCF::getLanguage()->get($this->groupName);
	}
	
	/**
	 * Sets the name of this user group.
	 * 
	 * This method is only needed to set the current name if it has been changed
	 * in the same request.
	 * 
	 * @param	string		$name
	 */
	public function setName($name) {
		$this->data['groupName'] = $name;
	}
	
	/**
	 * Returns true if current user may delete this group.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		// insufficient permissions
		if (!WCF::getSession()->getPermission('admin.user.canDeleteGroup')) return false;
		
		// cannot delete own groups
		if ($this->isMember()) return false;
		
		// user cannot delete this group
		if (!$this->isAccessible()) return false;
		
		// cannot delete static groups
		if ($this->groupType == self::EVERYONE || $this->groupType == self::GUESTS || $this->groupType == self::USERS) return false;
		
		return true;
	}
	
	/**
	 * Returns true if current user may edit this group.
	 * 
	 * @return	boolean
	 */
	public function isEditable() {
		// insufficient permissions
		if (!WCF::getSession()->getPermission('admin.user.canEditGroup')) return false;
		
		// user cannot edit this group
		if (!$this->isAccessible()) return false;
		
		return true;
	}
	
	/**
	 * Returns the value of the group option with the given name.
	 * 
	 * @param	string		$name
	 * @return	mixed
	 */
	public function getGroupOption($name) {
		if ($this->groupOptions === null) {
			// get all options and filter options with low priority
			$this->groupOptions = $groupOptionIDs = array();
			
			$sql = "SELECT		optionName, optionID
				FROM		wcf".WCF_N."_user_group_option";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			while ($row = $statement->fetchArray()) {
				$groupOptionIDs[$row['optionName']] = $row['optionID'];
			}
			
			if (!empty($groupOptionIDs)) {
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("option_value.groupID = ?", array($this->groupID));
				$conditions->add("option_value.optionID IN (?)", array($groupOptionIDs));
				
				$sql = "SELECT		group_option.optionName, option_value.optionValue
					FROM		wcf".WCF_N."_user_group_option_value option_value
					LEFT JOIN	wcf".WCF_N."_user_group_option group_option
					ON		(group_option.optionID = option_value.optionID)
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				while ($row = $statement->fetchArray()) {
					$this->groupOptions[$row['optionName']] = $row['optionValue'];
				}
			}
		}
		
		if (isset($this->groupOptions[$name])) {
			return $this->groupOptions[$name];
		}
		
		return null;
	}
}
