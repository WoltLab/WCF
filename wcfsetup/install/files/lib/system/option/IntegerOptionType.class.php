<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for integer input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class IntegerOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\TextOptionType::$inputClass
	 */
	protected $inputClass = 'short';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'inputClass' => $this->inputClass,
			'value' => $value
		));
		
		return WCF::getTPL()->fetch('integerOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return intval($newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if ($option->minvalue !== null && $option->minvalue > $newValue) {
			throw new UserInputException($option->optionName, 'tooLow');
		}
		if ($option->maxvalue !== null && $option->maxvalue < $newValue) {
			throw new UserInputException($option->optionName, 'tooHigh');
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::compare()
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		return ($value1 > $value2) ? 1 : -1;
	}
}
