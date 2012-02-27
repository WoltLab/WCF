<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * InfiniteInverseIntegerUserGroupOptionType is an implementation of IUserGroupOptionType
 * for integer values.
 * The merge of option values returns -1 if all values are -1 otherwise the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class InfiniteInverseIntegerUserGroupOptionType extends InverseIntegerUserGroupOptionType {
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $key => $value) {
			if ($value == -1) unset($values[$key]);
		}
		
		if (count($values) == 0) return -1;
		return min($values);
	}
	
	/**
	* @see wcf\system\option\user\group\IUserGroupOptionType::checkPermissions()
	*/
	public function checkPermissions(Option $option, $newValue) {
		if (
			(WCF::getSession()->getPermission($option->optionName) == -1 && $newValue != -1) ||
			$newValue < WCF::getSession()->getPermission($option->optionName)
		) {
			throw new UserInputException($option->optionName, 'permissionsDenied');
		}
	}
}
