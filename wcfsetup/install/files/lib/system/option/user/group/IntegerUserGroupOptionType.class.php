<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\IntegerOptionType;
use wcf\system\WCF;

/**
 * IntegerUserGroupOptionType is an implementation of IUserGroupOptionType for integer values.
 * The merge of option values returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class IntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\user.group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		return max($values);
	}
	
	/**
	* @see wcf\system\option\user\group\IUserGroupOptionType::checkPermissions()
	*/
	public function checkPermissions(Option $option, $newValue) {
		if ($newValue > WCF::getSession()->getPermission($option->optionName)) {
			throw new UserInputException($option->optionName, 'permissionsDenied');
		}
	}
}
