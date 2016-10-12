<?php
namespace wcf\system\option\user\group;

/**
 * User group option type implementation for integer input fields.
 * 
 * The merge of option values returns -1 if all values are -1 otherwise the lowest
 * value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class InfiniteInverseIntegerUserGroupOptionType extends InverseIntegerUserGroupOptionType {
	/**
	 * @inheritDoc
	 */
	public function merge($defaultValue, $groupValue) {
		if ($groupValue == -1 || $defaultValue == $groupValue) {
			return null;
		}
		
		if ($defaultValue == -1) {
			return $groupValue;
		}
		
		return min($defaultValue, $groupValue);
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
		
		return ($value1 < $value2) ? 1 : -1;
	}
}
