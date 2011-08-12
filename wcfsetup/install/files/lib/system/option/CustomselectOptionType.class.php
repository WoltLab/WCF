<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;

/**
 * OptionTypeSelect is an implementation of IOptionType for 'select' tags with a
 * text field for custom inputs.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class CustomselectOptionType extends SelectOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'selectOptions' => $option->parseSelectOptions(),
			'value' => $value,
			'customValue' => (!isset($options[$value]) ? $value : '')
		));
		
		return WCF::getTPL()->fetch('optionTypeCustomselect');
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {}
	
	/**
	 * @see wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (empty($newValue) && isset($_POST['values'][$option->optionName.'_custom'])) {
			return $_POST['values'][$option->optionName.'_custom'];
		}
		return $newValue;
	}
}
