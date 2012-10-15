<?php
namespace wcf\system\option\user\group;
use wcf\system\option\BooleanOptionType;

/**
 * BooleanUserGroupOptionType is an implementation of IUserGroupOptionType for
 * boolean values.
 * The merge of option values returns true, if at least one value is true.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class BooleanUserGroupOptionType extends BooleanOptionType implements IUserGroupOptionType {
	/**
	 * @see	wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		// don't save if values are equal or $defaultValue is better
		if ($defaultValue == $groupValue || $defaultValue && !$groupValue) {
			return null;
		}
		
		return $groupValue;
	}
}
