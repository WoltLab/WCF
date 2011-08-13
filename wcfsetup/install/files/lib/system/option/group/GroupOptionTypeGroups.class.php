<?php
namespace wcf\system\option\group;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\option\AbstractOptionType;
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
class GroupOptionTypeGroups extends AbstractOptionType implements IGroupOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		// get selected group
		$selectedGroups = explode(',', $value);
		
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// generate html
		$html = '';
		foreach ($groups as $group) {
			$html .= '<label><input type="checkbox" name="values['.StringUtil::encodeHTML($option->optionName).'][]" value="'.$group->groupIdentifier.'" '.(in_array($group->groupIdentifier, $selectedGroups) ? 'checked="checked" ' : '').'/> '.StringUtil::encodeHTML($group->__toString()).'</label>';
		}
		
		return $html;
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		// get all groups
		$groups = UserGroup::getGroupsByType();
		
		// get new value
		if (!is_array($newValue)) $newValue = array();
		
		// check groups
		foreach ($newValue as $groupIdentifier) {
			$found = false;
			foreach ($groups as $group) {
				if ($group->groupIdentifier == $groupIdentifier) {
					$found = true;
					break;
				}
			}
			
			if (!$found) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		sort($newValue);
		return implode(',', $newValue);
	}
	
	/**
	 * @see wcf\system\option\group\IGroupOptionType::merge()
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
