<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;

/**
 * Option type implementation for date input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class DateOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	protected $inputType = 'date';
	
	/**
	 * @inheritDoc
	 */
	protected $inputClass = '';
	
	/**
	 * @inheritDoc
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
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		return (strtotime($value1) > strtotime($value2)) ? 1 : -1;
	}
}
