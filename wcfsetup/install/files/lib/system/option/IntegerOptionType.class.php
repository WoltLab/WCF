<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for integer input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class IntegerOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	protected $inputClass = 'short';
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign([
			'option' => $option,
			'inputClass' => $this->inputClass,
			'value' => $value
		]);
		
		return WCF::getTPL()->fetch('integerOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		return intval($newValue);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		return ($value1 > $value2) ? 1 : -1;
	}
}
