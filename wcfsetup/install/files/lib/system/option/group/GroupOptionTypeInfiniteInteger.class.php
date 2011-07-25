<?php
namespace wcf\system\option\group;

/**
 * GroupOptionTypeInfiniteinteger is an implementation of GroupOptionType for integer values with the infinite option.
 * The merge of option values returns true, if at least one value is -1. Otherwise it returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class GroupOptionTypeInfiniteInteger extends GroupOptionTypeInteger {
	/**
	 * @see wcf\system\option\group\IGroupOptionType::merge()
	 */
	public function merge(array $values) {
		if (in_array(-1, $values)) return -1;
		return parent::merge($values);
	}
}
