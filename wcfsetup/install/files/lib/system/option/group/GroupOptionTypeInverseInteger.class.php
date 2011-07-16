<?php
namespace wcf\system\option\group;
use wcf\system\option\OptionTypeInteger;

/**
 * GroupOptionTypeInverseInteger is an implementation of GroupOptionType for integer values.
 * The merge of option values returns the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class GroupOptionTypeInverseInteger extends OptionTypeInteger implements GroupOptionType {
	/**
	 * @see wcf\system\option\group\GroupOptionType::merge()
	 */
	public function merge(array $values) {
		return min($values);
	}
}
