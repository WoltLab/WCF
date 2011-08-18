<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\OptionUtil;

/**
 * MultiselectOptionType is an implementation of IOptionType for multiple 'select' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class MultiselectOptionType extends SelectOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(array &$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = explode("\n", $optionData['defaultValue']);
			else $optionData['optionValue'] = array();
		}
		else if (!is_array($optionData['optionValue'])) {
			$optionData['optionValue'] = explode("\n", $optionData['optionValue']);
		}
		
		// get options
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		
		WCF::getTPL()->assign(array(
			'optionData' => $optionData,
			'options' => $options
		));
		return WCF::getTPL()->fetch('optionTypeMultiselect');
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(array $optionData, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		foreach ($newValue as $value) {
			if (!isset($options[$value])) throw new UserInputException($optionData['optionName'], 'validationFailed');
		}
	}
	
	/**
	 * @see wcf\system\option\IOptionType::getData()
	 */
	public function getData(array $optionData, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		return implode("\n", $newValue);
	}
	
	/**
	 * @see wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(array &$optionData) {
		return $this->getFormElement($optionData);
	}
	
	/**
	 * @see wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $options, $value) {
		if (!is_array($value) || !count($value)) return false;
		$value = ArrayUtil::trim($value);
		if (!count($value)) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", array(implode("\n", $value)));
		return true;
	}
}
