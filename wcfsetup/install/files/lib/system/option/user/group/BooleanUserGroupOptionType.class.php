<?php
namespace wcf\system\option\user\group;
use wcf\system\option\BooleanOptionType;

/**
 * User group option type implementation for boolean values.
 * 
 * The merge of option values returns true if at least one value is true.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class BooleanUserGroupOptionType extends BooleanOptionType implements IUserGroupOptionType {
	/**
	 * @see	\wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		// don't save if values are equal or $defaultValue is better
		if ($defaultValue == $groupValue || $defaultValue && !$groupValue) {
			return null;
		}
		
		return $groupValue;
	}
}
