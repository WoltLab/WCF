<?php
namespace wcf\system\option\user\group;
use wcf\system\option\IntegerOptionType;

/**
 * User group option type implementation for integer input fields.
 * 
 * The merge of option values returns the lowest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class InverseIntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType {
	/**
	 * @see	wcf\system\option\user\group\IUserGroupOptionType::diff()
	 */
	public function diff($defaultValue, $groupValue) {
		return $this->merge($defaultValue, $groupValue);
	}
	
	/**
	 * @see	wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		if ($defaultValue < $groupValue) {
			return null;
		}
		
		return $groupValue;
	}
}
