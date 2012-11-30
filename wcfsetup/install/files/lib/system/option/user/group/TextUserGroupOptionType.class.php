<?php
namespace wcf\system\option\user\group;
use wcf\system\option\TextOptionType;
use wcf\util\StringUtil;

/**
 * User group option type implementation for textual input fields.
 * 
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class TextUserGroupOptionType extends TextOptionType implements IUserGroupOptionType {
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
