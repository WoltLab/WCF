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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class TextareaUserGroupOptionType extends TextareaOptionType implements IUserGroupOptionType {
	/**
	 * @inheritDoc
	 */
	public function merge($defaultValue, $groupValue) {
		$defaultValue = empty($defaultValue) ? [] : explode("\n", StringUtil::unifyNewlines($defaultValue));
		$groupValue = empty($groupValue) ? [] : explode("\n", StringUtil::unifyNewlines($groupValue));
		
		return implode("\n", array_unique(array_merge($defaultValue, $groupValue)));
	}
}
