<?php
namespace wcf\data\user\group;
use wcf\data\DatabaseObjectEditor;
use wcf\data\EditableCachedObject;
use wcf\data\acp\session\ACPSession;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit user groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category 	Community Framework
 */
class UserGroupEditor extends DatabaseObjectEditor implements EditableCachedObject {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\group\UserGroup';
	
	/**
	 * @see	EditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$group = parent::create($parameters);
		
		// update accessible groups
		self::updateAccessibleGroups($group->groupID);
		
		return $group;
	}
	
	/**
	 * @see	DatabaseObjectEditor::__deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		parent::deleteAll($objectIDs);
		
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
		if (!count($groupIDs)) return;
		
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
		if (!count($groupIDs)) return;
		
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
	}
	
	/**
	 * Updates the value from the accessiblegroups option.
	 * 
	 * @param	integer		$groupID	this group is added or deleted in the value 	
	 * @param 	boolean		$delete		flag for group deletion
	 */
	protected static function updateAccessibleGroups($groupID, $delete = false) {
		if ($delete) {
			$sql = "UPDATE	wcf".WCF_N."_group_option_value
					SET	optionValue = ?
					WHERE	groupID = ?
					AND	optionID = ?";
			$updateStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "SELECT		groupID, optionValue, groupOption.optionID
				FROM		wcf".WCF_N."_group_option groupOption
				LEFT JOIN	wcf".WCF_N."_group_option_value optionValue
				ON		(groupOption.optionID = optionValue.optionID)
				WHERE		groupOption.optionname = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array('admin.user.accessibleGroups'));
			while ($row = $statement->fetchArray($result)) {
				$valueIDs = explode(',', $row['optionValue']);
				if (in_array($groupID, $valueIDs)) {
					$key = array_keys($valueIDs, $groupID);
					if (!empty($key)) unset($valueIDs[$key[0]]);
					$updateIDs = implode(",", $valueIDs); 
					
					$updateStatement->execute(array(implode(',', $valueIDs), $row['groupID'], $row['optionID']));
				}
			}
			
			return;
		}
		
		// get existing groups
		$groupIDs = array();
		$sql = "SELECT		groupID
			FROM		wcf".WCF_N."_user_group
			ORDER BY	groupID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if ($row['groupID'] == $groupID) continue;
			$groupIDs[] = $row['groupID'];
		}
		
		$optionID = 0;
		$targetGroupIDs = array();
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("groupOption.optionName = ?", array('admin.user.accessibleGroups'));
		$conditions->add("groupID IN (?)", array($groupIDs));
		
		$sql = "SELECT		groupID, optionValue, groupOption.optionID
			FROM		wcf".WCF_N."_user_group_option groupOption
			LEFT JOIN	wcf".WCF_N."_user_group_option_value optionValue
			ON		(groupOption.optionID = optionValue.optionID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$updateGroupIDs = array();
		$optionID = 0;
		// get groups which got "accessibleGroups"-option with all groupIDs
		while ($row = $statement->fetchArray()) {
			
			// check for differences in options-groups and existing-groups	
			$optionGroupIDs = explode(',', $row['optionValue']);
			$differences = array_diff($optionGroupIDs, $groupIDs);
			
			// get groups which got the right to change all groups			
			if (empty($differences) && (count($optionGroupIDs) == count($groupIDs))) {
				$updateGroupIDs[] = $row['groupID'];
				$optionID = $row['optionID'];
			}
		}
		
		// update optionValue from groups which got all existing groups as value
		if (count($updateGroupIDs)) {
			$groupIDs[] = $groupID;
			
			$sql = "UPDATE	wcf".WCF_N."_user_group_option_value
				SET	optionValue = ?
				WHERE	groupID IN (".implode(',', $updateGroupIDs).")
				AND 	optionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(implode(',', $groupIDs), $optionID));
		}
	}
	
	/**
	 * @see EditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		// clear cache
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.groups*.php', true);
		
		// clear sessions
		SessionHandler::resetSessions();
	}
}
?>
