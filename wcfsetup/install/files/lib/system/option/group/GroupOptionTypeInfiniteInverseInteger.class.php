<?php
namespace wcf\system\option\group;

/**
 * GroupOptionTypeInfiniteinverseinteger is an implementation of GroupOptionType for integer values.
 * The merge of option values returns -1 if all values are -1 otherwise the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class GroupOptionTypeInfiniteInverseInteger extends GroupOptionTypeInverseinteger {
	/**
	 * @see wcf\system\option\group\IGroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $key => $value) {
			if ($value == -1) unset($values[$key]);
		}
		
		if (count($values) == 0) return -1;
		return min($values);
	}
}
