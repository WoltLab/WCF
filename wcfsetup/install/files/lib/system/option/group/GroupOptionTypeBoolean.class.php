<?php
namespace wcf\system\option\group;
use wcf\system\option\OptionTypeBoolean;

/**
 * GroupOptionTypeBoolean is an implementation of GroupOptionType for boolean values.
 * The merge of option values returns true, if at least one value is true. Otherwise false.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class GroupOptionTypeBoolean extends OptionTypeBoolean implements GroupOptionType {
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $value) {
			if ($value) return true;
		}

		return false;
	}
}
?>