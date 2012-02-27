<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\TextareaOptionType;
use wcf\system\WCF;

/**
 * TextareaUserGroupOptionType is an implementation of IUserGroupOptionType for
 * text values.
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class TextareaUserGroupOptionType extends TextareaOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		$result = '';
		
		foreach ($values as $value) {
			if (!empty($result)) $result .= "\n";
			$result .= $value;
		}

		return $result;
	}
	
	/**
	* @see wcf\system\option\user\group\IUserGroupOptionType::checkPermissions()
	*/
	public function checkPermissions(Option $option, $newValue) {
		if (!strpos($newValue, WCF::getSession()->getPermission($option->optionName))) {
			throw new UserInputException($option->optionName, 'permissionsDenied');
		}
	}
}
