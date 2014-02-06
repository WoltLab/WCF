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
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class InfiniteIntegerUserGroupOptionType extends IntegerUserGroupOptionType {
	/**
	 * @see	\wcf\system\option\user\group\IUserGroupOptionType::merge()
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
}
