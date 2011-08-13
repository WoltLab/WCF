<?php
namespace wcf\system\option\userGroup;

/**
 * InfiniteInverseIntegerUserGroupOptionType is an implementation of IUserGroupOptionType
 * for integer values.
 * The merge of option values returns -1 if all values are -1 otherwise the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.userGroup
 * @category 	Community Framework
 */
class InfiniteInverseIntegerUserGroupOptionType extends InverseIntegerUserGroupOptionType {
	/**
	 * @see wcf\system\option\userGroup\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $key => $value) {
			if ($value == -1) unset($values[$key]);
		}
		
		if (count($values) == 0) return -1;
		return min($values);
	}
}
