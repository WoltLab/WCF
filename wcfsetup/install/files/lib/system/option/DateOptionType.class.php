<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;

/**
 * Option type implementation for date input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class DateOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\TextOptionType::$inputType
	 */
	protected $inputType = 'date';
	
	/**
	 * @see	\wcf\system\option\TextOptionType::$inputClass
	 */
	protected $inputClass = '';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function validate(Option $option, $newValue) {
		if (empty($newValue)) return;
		
		if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $newValue, $match)) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
		
		if (!checkdate(intval($match[2]), intval($match[3]), intval($match[1]))) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
}
