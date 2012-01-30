<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\util\ArrayUtil;

/**
 * MultiSelectOptionType is an implementation of IOptionType for multiple 'select' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class MultiSelectOptionType extends SelectOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'selectOptions' => $option->parseSelectOptions(),
			'value' => explode("\n", $value)
		));
		return WCF::getTPL()->fetch('multiSelectOptionType');
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$options = $option->parseSelectOptions();
		foreach ($newValue as $value) {
			if (!isset($options[$value])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		return implode("\n", $newValue);
	}
	
	/**
	 * @see wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (!is_array($value) || !count($value)) return false;
		$value = ArrayUtil::trim($value);
		if (!count($value)) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", array(implode("\n", $value)));
		return true;
	}
}
