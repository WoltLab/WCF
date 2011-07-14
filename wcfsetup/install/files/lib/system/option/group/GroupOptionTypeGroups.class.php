<?php
namespace wcf\system\option\group;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * GroupOptionTypeGroups generates a select-list of all available user groups.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class GroupOptionTypeGroups implements GroupOptionType {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		// get selected group
		$selectedGroups = explode(',', $value);
		
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// generate html
		$html = '';
		foreach ($groups as $group) {
			$html .= '<label><input type="checkbox" name="values['.StringUtil::encodeHTML($option->optionName).'][]" value="'.$group->groupID.'" '.(in_array($group->groupID, $selectedGroups) ? 'checked="checked" ' : '').'/> '.StringUtil::encodeHTML($group->groupName).'</label>';
		}
		
		return $html;
	}
	
	/**
	 * @see OptionType::validate()
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
	 * @see OptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$newValue = ArrayUtil::toIntegerArray($newValue);
		sort($newValue, SORT_NUMERIC);
		return implode(',', $newValue);
	}
	
	/**
	 * @see GroupOptionType::merge()
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
