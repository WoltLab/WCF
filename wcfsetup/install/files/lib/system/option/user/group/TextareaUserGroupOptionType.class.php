<?php
namespace wcf\system\option\user\group;
use wcf\system\option\TextareaOptionType;
use wcf\util\StringUtil;

/**
 * User group option type implementation for textareas.
 * 
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class TextareaUserGroupOptionType extends TextareaOptionType implements IUserGroupOptionType {
	/**
	 * @see	\wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		$defaultValue = empty($defaultValue) ? array() : explode("\n", StringUtil::unifyNewlines($defaultValue));
		$groupValue = empty($groupValue) ? array() : explode("\n", StringUtil::unifyNewlines($groupValue));
		
		return implode("\n", array_unique(array_merge($defaultValue, $groupValue)));
	}
}
