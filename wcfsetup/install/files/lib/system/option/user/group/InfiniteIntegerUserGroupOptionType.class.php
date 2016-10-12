<?php
namespace wcf\system\option\user\group;

/**
 * User group option type implementation for integer input fields with an option
 * for an infinite value.
 * 
 * The merge of option values returns true if at least one value is -1. Otherwise
 * it returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class InfiniteIntegerUserGroupOptionType extends IntegerUserGroupOptionType {
	/**
	 * @inheritDoc
	 */
	public function merge($defaultValue, $groupValue) {
		if ($defaultValue == -1) {
			return null;
		}
		else if ($groupValue == -1) {
			return $groupValue;
		}
		else {
			return parent::merge($defaultValue, $groupValue);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		if ($value1 == -1) {
			return 1;
		}
		else if ($value2 == -1) {
			return -1;
		}
		
		return parent::compare($value1, $value2);
	}
}
