<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\option\OptionTypeText;

/**
 * OptionTypeText is an implementation of OptionType for integer fields.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class OptionTypeInteger extends OptionTypeText {
	/**
	 * @see OptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return intval($newValue);
	}
}
