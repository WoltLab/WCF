<?php
namespace wcf\system\option\user\group;
use wcf\system\option\IntegerOptionType;

/**
 * User group option type implementation for integer input fields.
 * 
 * The merge of option values returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class IntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType {
	/**
	 * @see	\wcf\system\option\user.group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		if ($groupValue > $defaultValue) {
			return $groupValue;
		}
		
		return null;
	}
}
