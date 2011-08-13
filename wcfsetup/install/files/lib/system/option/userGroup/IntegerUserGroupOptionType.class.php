<?php
namespace wcf\system\option\userGroup;
use wcf\system\option\IntegerOptionType;

/**
 * IntegerUserGroupOptionType is an implementation of IUserGroupOptionType for integer values.
 * The merge of option values returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.userGroup
 * @category 	Community Framework
 */
class IntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\userGroup\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		return max($values);
	}
}
