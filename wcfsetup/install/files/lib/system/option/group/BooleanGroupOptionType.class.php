<?php
namespace wcf\system\option\group;
use wcf\system\option\BooleanOptionType;

/**
 * BooleanGroupOptionType is an implementation of IGroupOptionType for boolean values.
 * The merge of option values returns true, if at least one value is true. Otherwise false.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class BooleanGroupOptionType extends BooleanOptionType implements IGroupOptionType {
	/**
	 * @see wcf\system\option\group\IGroupOptionType::merge()
	 */
	public function merge(array $values) {
		foreach ($values as $value) {
			if ($value) return true;
		}

		return false;
	}
}
