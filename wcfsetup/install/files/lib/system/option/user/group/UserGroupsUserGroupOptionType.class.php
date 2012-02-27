<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\exception\UserInputException;
use wcf\system\option\AbstractOptionType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * UserGroupsUserGroupOptionType generates a select-list of all available user groups.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class UserGroupsUserGroupOptionType extends AbstractOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		// get selected group
		$selectedGroups = explode(',', $value);
		
		// get all groups
		$groups = UserGroup::getGroupsByType();
		$possibleGroups = explode(",", WCF::getSession()->getPermission($option->optionName));
		
		// generate html
		$html = '<fieldset><dl><dd>';
		foreach ($groups as $group) {
			if (!UserGroupOptionHandler::usePermissionCheck() || in_array($group->groupID, $possibleGroups)) {
				$html .= '<label><input type="checkbox" name="values['.StringUtil::encodeHTML($option->optionName).'][]" value="'.$group->groupID.'" '.(in_array($group->groupID, $selectedGroups) ? 'checked="checked" ' : '').'/> '.StringUtil::encodeHTML($group->groupName).'</label>';
			}
		}
		
		if ($html == '<fieldset><dl><dd>') {
			return null;
		}
		return $html.'</dd></dl></fieldset>';
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// get new value
		if (!is_array($newValue)) $newValue = array();
		$selectedGroups = ArrayUtil::toIntegerArray($newValue);
		
		// check groups
		foreach ($selectedGroups as $groupID) {
			if (!isset($groups[$groupID])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	* @see wcf\system\option\user\group\IUserGroupOptionType::checkPermissions()
	*/
	public function checkPermissions(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$selectedGroups = ArrayUtil::toIntegerArray($newValue);
		
		foreach ($selectedGroups as $selectedGroup) {
			if (!in_array($selectedGroup, explode(",", WCF::getSession()->getPermission($option->optionName)))) {
				throw new UserInputException($option->optionName, 'permissionsDenied');
			}
		}
	}
	
	/**
	 * @see wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$newValue = ArrayUtil::toIntegerArray($newValue);
		sort($newValue, SORT_NUMERIC);
		return implode(',', $newValue);
	}
	
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		$result = array();
		foreach ($values as $value) {
			$value = explode(',', $value);
			$result = array_merge($result, $value);
		}
		
		$result = array_unique($result);

		return implode(',', $result);
	}
}
