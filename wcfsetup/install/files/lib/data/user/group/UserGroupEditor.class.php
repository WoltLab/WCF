<?php
namespace wcf\data\user\group;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserGroupCacheBuilder;
use wcf\system\cache\builder\UserGroupPermissionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit user groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category	Community Framework
 */
class UserGroupEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\group\UserGroup';
	
	/**
	 * @see	\wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$group = parent::create($parameters);
		
		// update accessible groups
		self::updateAccessibleGroups($group->groupID);
		
		return $group;
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::__deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		$returnValue = parent::deleteAll($objectIDs);
		
		// remove user to group assignments
		self::removeGroupAssignments($objectIDs);
		
		// remove group option values
		self::removeOptionValues($objectIDs);
		
		foreach ($objectIDs as $objectID) {
			self::updateAccessibleGroups($objectID, true);
		}
		
		return $returnValue;
	}
	
	/**
	 * Removes user to group assignments.
	 * 
	 * @param	array		$groupIDs
	 */
	protected static function removeGroupAssignments(array $groupIDs) {
		if (empty($groupIDs)) return;
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_group
			WHERE		groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($groupIDs as $groupID) {
			$statement->execute(array($groupID));
		}
	}
	
	/**
	 * Removes group option values.
	 * 
	 * @param	array		$groupIDs
	 */
	protected static function removeOptionValues(array $groupIDs) {
		if (empty($groupIDs)) return;
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_group_option_value
			WHERE		groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($groupIDs as $groupID) {
			$statement->execute(array($groupID));
		}
	}
	
	/**
	 * Updates group options.
	 * 
	 * @param	array		$groupOptions
	 */
	public function updateGroupOptions(array $groupOptions = array()) {
		WCF::getDB()->beginTransaction();
		// delete old group options
		$sql = "DELETE FROM	wcf".WCF_N."_user_group_option_value
			WHERE		groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->groupID));
		
		// insert new options
		$sql = "INSERT INTO	wcf".WCF_N."_user_group_option_value
					(groupID, optionID, optionValue)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($groupOptions as $id => $value) {
			$statement->execute(array($this->groupID, $id, $value));
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Updates the value from the accessiblegroups option.
	 * 
	 * @param	integer		$groupID	this group is added or deleted in the value
	 * @param	boolean		$delete		flag for group deletion
	 */
	protected static function updateAccessibleGroups($groupID, $delete = false) {
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_group_option
			WHERE	optionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('admin.user.accessibleGroups'));
		$optionID = $statement->fetchColumn();
		$statement->closeCursor();
		
		if (!$optionID) throw new SystemException("Unable to find 'admin.user.accessibleGroups' user option");
		
		$userGroupList = new UserGroupList();
		$userGroupList->getConditionBuilder()->add('user_group.groupID <> ?', array($groupID));
		$userGroupList->readObjects();
		$groupIDs = array();
		foreach ($userGroupList as $userGroup) {
			$groupIDs[] = $userGroup->groupID;
		}
		
		$sql = "UPDATE	wcf".WCF_N."_user_group_option_value
			SET	optionValue = ?
			WHERE		groupID = ?
				AND	optionID = ?";
		$updateStatement = WCF::getDB()->prepareStatement($sql);
		
		$sql = "SELECT		groupID, optionValue
			FROM		wcf".WCF_N."_user_group_option_value
			WHERE		optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($optionID));
		while ($row = $statement->fetchArray()) {
			$valueIDs = explode(',', $row['optionValue']);
			if ($delete) {
				$valueIDs = array_filter($valueIDs, function ($item) use ($groupID) {
					return $item != $groupID;
				});
			}
			else {
				if (count(array_diff($groupIDs, $valueIDs)) == 0) {
					$valueIDs[] = $groupID;
				}
			}
			
			$updateStatement->execute(array(implode(',', $valueIDs), $row['groupID'], $optionID));
		}
	}
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		// clear cache
		UserGroupCacheBuilder::getInstance()->reset();
		UserGroupPermissionCacheBuilder::getInstance()->reset();
		
		// clear sessions
		SessionHandler::resetSessions();
	}
}
