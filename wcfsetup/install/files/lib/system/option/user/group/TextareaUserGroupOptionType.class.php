<?php
namespace wcf\system\option\user\group;
use wcf\system\option\TextareaOptionType;
use wcf\util\StringUtil;

/**
 * TextareaUserGroupOptionType is an implementation of IUserGroupOptionType for
 * text values.
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class TextareaUserGroupOptionType extends TextareaOptionType implements IUserGroupOptionType {
	/**
	 * @see	wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		$defaultValue = explode("\n", StringUtil::unifyNewlines($defaultValue));
		$groupValue = explode("\n", StringUtil::unifyNewlines($groupValue));
	
		$result = array_diff($groupValue, $defaultValue);
		if (empty($result)) {
			return null;
		}
	
		return implode("\n", $result);
	}
}
