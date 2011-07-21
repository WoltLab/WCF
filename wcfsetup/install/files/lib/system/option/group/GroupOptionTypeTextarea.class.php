<?php
namespace wcf\system\option\group;
use wcf\system\option\OptionTypeTextarea;

/**
 * GroupOptionTypeTextarea is an implementation of GroupOptionType for text values.
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class GroupOptionTypeTextarea extends OptionTypeTextarea implements IGroupOptionType {
	/**
	 * @see wcf\system\option\group\IGroupOptionType::merge()
	 */
	public function merge(array $values) {
		$result = '';
		
		foreach ($values as $value) {
			if (!empty($result)) $result .= "\n";
			$result .= $value;
		}

		return $result;
	}
}
