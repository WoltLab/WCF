<?php
namespace wcf\system\option\group;
use wcf\system\option\TextOptionType;

/**
 * TextGroupOptionType is an implementation of IGroupOptionType for text values.
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.group
 * @category 	Community Framework
 */
class TextGroupOptionType extends TextOptionType implements IGroupOptionType {
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
