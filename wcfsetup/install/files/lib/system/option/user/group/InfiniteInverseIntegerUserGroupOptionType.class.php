<?php
namespace wcf\system\option\user\group;

/**
 * InfiniteInverseIntegerUserGroupOptionType is an implementation of IUserGroupOptionType
 * for integer values.
 * The merge of option values returns -1 if all values are -1 otherwise the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class InfiniteInverseIntegerUserGroupOptionType extends InverseIntegerUserGroupOptionType {
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		if (($defaultValue == -1 && $groupValue == -1) || ($defaultValue == $groupValue)) {
			return null;
		}
		
		return min($defaultValue, $groupValue);
	}
}
