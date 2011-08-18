<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;

/**
 * TextareaOptionType is an implementation of IOptionType for 'textarea' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class TextareaOptionType extends TextOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'value' => $value
		));
		return WCF::getTPL()->fetch('optionTypeTextarea');
	}
}
