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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class UserGroupsUserGroupOptionType extends AbstractOptionType implements IUserGroupOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		// get selected group
		$selectedGroups = explode(',', $value);
		
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// generate html
		$html = '';
		foreach ($groups as $group) {
			$html .= '<label><input type="checkbox" name="values['.StringUtil::encodeHTML($option->optionName).'][]" value="'.$group->groupID.'"'.(in_array($group->groupID, $selectedGroups) ? ' checked' : '').'> '.$group->getName().'</label>';
		}
		
		return $html;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// get new value
		if (!is_array($newValue)) $newValue = [];
		$selectedGroups = ArrayUtil::toIntegerArray($newValue);
		
		// check groups
		foreach ($selectedGroups as $groupID) {
			if (!isset($groups[$groupID])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		$newValue = ArrayUtil::toIntegerArray($newValue);
		sort($newValue, SORT_NUMERIC);
		return implode(',', $newValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function merge($defaultValue, $groupValue) {
		$defaultValue = empty($defaultValue) ? [] : explode(',', StringUtil::unifyNewlines($defaultValue));
		$groupValue = empty($groupValue) ? [] : explode(',', StringUtil::unifyNewlines($groupValue));
		
		return implode(',', array_unique(array_merge($defaultValue, $groupValue)));
	}
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		$value1 = ($value1) ? explode(',', $value1) : [];
		$value2 = ($value2) ? explode(',', $value2) : [];
		
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
