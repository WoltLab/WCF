<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\exception\UserInputException;
use wcf\system\option\AbstractOptionType;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * User group option type implementation for a user group select list.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class UserGroupsUserGroupOptionType extends AbstractOptionType implements IUserGroupOptionType {
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		// get selected group
		$selectedGroups = explode(',', $value);
		
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// generate html
		$html = '';
		foreach ($groups as $group) {
			$html .= '<label><input type="checkbox" name="values['.StringUtil::encodeHTML($option->optionName).'][]" value="'.$group->groupID.'" '.(in_array($group->groupID, $selectedGroups) ? 'checked="checked" ' : '').'/> '.$group->getName().'</label>';
		}
		
		return $html;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
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
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$newValue = ArrayUtil::toIntegerArray($newValue);
		sort($newValue, SORT_NUMERIC);
		return implode(',', $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		$defaultValue = empty($defaultValue) ? array() : explode(',', StringUtil::unifyNewlines($defaultValue));
		$groupValue = empty($groupValue) ? array() : explode(',', StringUtil::unifyNewlines($groupValue));
		
		return implode(',', array_unique(array_merge($defaultValue, $groupValue)));
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::compare()
	 */
	public function compare($value1, $value2) {
		$value1 = ($value1) ? explode(',', $value1) : array();
		$value2 = ($value2) ? explode(',', $value2) : array();
		
		// check if value1 contains more elements than value2
		$diff = array_diff($value1, $value2);
		if (!empty($diff)) {
			return 1;
		}
		
		// check if value1 contains less elements than value2
		$diff = array_diff($value2, $value1);
		if (!empty($diff)) {
			return -1;
		}
		
		// both lists are equal
		return 0;
	}
}
