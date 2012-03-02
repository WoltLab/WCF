<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\BooleanOptionType;
use wcf\system\WCF;

/**
 * BooleanUserGroupOptionType is an implementation of IUserGroupOptionType for boolean values.
 * The merge of option values returns true, if at least one value is true. Otherwise false.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class BooleanUserGroupOptionType extends BooleanOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $value) {
			if ($value) return true;
		}

		return false;
	}
	
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::checkPermissions()
	 */
	public function checkPermissions(Option $option, $newValue) {
		if ($newValue && !WCF::getSession()->getPermission($option->optionName)) {
			throw new UserInputException($option->optionName, 'permissionsDenied');
		}
	}
}
