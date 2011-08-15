<?php
namespace wcf\system\option\user\group;

/**
 * InfiniteIntegerUserGroupOptionType is an implementation of IUserGroupOptionType
 * for integer values with the infinite option.
 * The merge of option values returns true, if at least one value is -1. Otherwise
 * it returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class InfiniteIntegerUserGroupOptionType extends IntegerUserGroupOptionType {
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		if (in_array(-1, $values)) return -1;
		return parent::merge($values);
	}
}
