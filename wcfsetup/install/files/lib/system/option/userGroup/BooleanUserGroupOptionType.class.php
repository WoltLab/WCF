<?php
namespace wcf\system\option\userGroup;
use wcf\system\option\BooleanOptionType;

/**
 * BooleanUserGroupOptionType is an implementation of IUserGroupOptionType for boolean values.
 * The merge of option values returns true, if at least one value is true. Otherwise false.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.userGroup
 * @category 	Community Framework
 */
class BooleanUserGroupOptionType extends BooleanOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\userGroup\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $value) {
			if ($value) return true;
		}

		return false;
	}
}
