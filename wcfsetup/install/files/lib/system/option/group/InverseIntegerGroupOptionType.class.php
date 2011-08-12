<?php
namespace wcf\system\option\group;
use wcf\system\option\IntegerOptionType;

/**
 * InverseIntegerGroupOptionType is an implementation of IGroupOptionType for integer values.
 * The merge of option values returns the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class InverseIntegerGroupOptionType extends IntegerOptionType implements IGroupOptionType {
	/**
	 * @see wcf\system\option\group\IGroupOptionType::merge()
	 */
	public function merge(array $values) {
		return min($values);
	}
}
