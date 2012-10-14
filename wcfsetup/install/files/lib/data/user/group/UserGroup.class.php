<?php
namespace wcf\data\user\group;
use wcf\data\user\User;
use wcf\data\DatabaseObject;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a user group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category 	Community Framework
 */
class UserGroup extends DatabaseObject {
	/**
	 * group type everyone user group
	 * @var integer
	 */
	const EVERYONE = 1;
	
	/**
	 * group type guests user group
	 * @var integer
	 */
	const GUESTS = 2;
	
	/**
	 * group type registered users user group
	 * @var integer
	 */
	const USERS = 3;
	
	/**
	 * group type of other user groups
	 * @var integer
	 */
	const OTHER = 4;
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_group';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'groupID';
	
	/**
	 * Caches groups.
	 * 
	 * @var	array<UserGroup>
	 */
	protected static $cache = null;
	
	/**
	 * list of accessible groups for active user.
	 * 
	 * @param	array<integer>
	 */
	protected static $accessibleGroups = null;
	
	/**
	 * Cached group options of this group.
	 * 
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
	 * @return	array<UserGroup>
	 */
	public static function getGroupsByType(array $types = array()) {
		self::getCache();
		// get all groups
		if (!count($types)) return self::$cache['groups'];
		
		// get groups by type
		$groupIDs = self::getGroupIDsByType($types);
		$groups = array();
		
		foreach ($groupIDs as $groupID) {
			$groups[$groupID] = self::$cache['groups'][$groupID];
		}
		
		return $groups;
	}
	
	/**
	 * Returns unique group by given type. Only works for the default user groups.
	 * 
	 * @param	integer		$type
	 * @return	UserGroup
	 */
	public static function getGroupByType($type) {
		if ($type != self::EVERYONE && $type != self::GUESTS && $type != self::USERS) {
			throw new SystemException('invalid value for type argument');
		}
		
		$groups = self::getGroupsByType(array($type));
		return array_shift($groups);
	}
	
	/**
	 * Returns true, if the given user is member of the group.
	 * 
	 * @param	wcf\data\user\User	$user	WCF::getUser() is omitted
	 * @return	boolean
	 */
	public function isMember(User $user = null) {
		if ($user === null) $user = WCF::getUser();
		
		if (in_array($this->groupID, $user->getGroupIDs())) return true;
		return false;
	}
	
	/**
	 * Returns true, if the given groups are accessible for the active user.
	 * 
	 * @param	array		$groupIDs
	 * @return 	boolean
	 */
	public static function isAccessibleGroup(array $groupIDs = array()) {
		if (self::$accessibleGroups === null) {
			self::$accessibleGroups = explode(',', WCF::getSession()->getPermission('admin.user.accessibleGroups'));
		}
		
		if (count($groupIDs) == 0) return false;
		
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
	 * @return	array<UserGroup>
	 */
	public static function getAccessibleGroups(array $groupTypes = array(), array $invalidGroupTypes = array()) {
		$groups = self::getGroupsByType($groupTypes);
		
		if (count($invalidGroupTypes) > 0) {
			$invalidGroups = self::getGroupsByType($invalidGroupTypes);
			foreach ($invalidGroups as $groupID => $group) {
				unset($groups[$groupID]);
			}
		}
		
		foreach ($groups as $key => $value) {
			if (!self::isAccessibleGroup(array($key))) {
				unset($groups[$key]);
			}
		}
		
		return $groups;
	}
	
	/**
	 * Loads the group cache.
	 */
	protected static function getCache() {
		if (self::$cache === null) {
			CacheHandler::getInstance()->addResource(
				'usergroups',
				WCF_DIR.'cache/cache.userGroups.php',
				'wcf\system\cache\builder\UserGroupCacheBuilder'
			);
			self::$cache = CacheHandler::getInstance()->get('usergroups');
		}
	}
	
	/**
	 * Returns true, if this group is accessible for the active user.
	 * 
	 * @return 	boolean
	 */
	public function isAccessible() {
		return self::isAccessibleGroup(array($this->groupID));
	}
	
	/**
	 * @see	wcf\data\user\group\UserGroup::getName()
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
		// TODO: Is the output correct when I18n is not used?
		return WCF::getLanguage()->get('wcf.acp.group.group'.$this->groupID);
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
		if ($this->groupType == UserGroup::EVERYONE || $this->groupType == UserGroup::GUESTS || $this->groupType == UserGroup::USERS) return false;
		
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
			$groupOptionIDs = array();
			$sql = "SELECT		optionName, optionID 
				FROM		wcf".WCF_N."_user_group_option option_table
				LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
				ON		(package_dependency.dependency = option_table.packageID)
				WHERE 		package_dependency.packageID = ?
				ORDER BY	package_dependency.priority ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(PACKAGE_ID));
			while ($row = $statement->fetchArray()) {
				$groupOptionIDs[$row['optionName']] = $row['optionID'];
			}
			
			if (count($groupOptionIDs)) {
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
