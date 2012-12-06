<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Option type implementation for integer input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class IntegerOptionType extends TextOptionType {
	/**
	 * @see	wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return intval($newValue);
	}
}
