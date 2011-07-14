<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\option\OptionType;
use wcf\system\option\SearchableUserOption;
use wcf\system\WCF;
use wcf\system\UserInputException;
use wcf\util\StringUtil;

/**
 * OptionTypeRadiobuttons is an implementation of OptionType for 'input type="radio"' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class OptionTypeRadiobuttons implements OptionType, SearchableUserOption {
	public $templateName = 'optionTypeRadiobuttons';

	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		// get options
		$selectOptions = $option->parseSelectOptions();

		$availableOptions = $option->parseMultipleEnableOptions();
		$options = array(
			'disableOptions' => array(),
			'enableOptions' => array()
		);
		
		foreach ($availableOptions as $key => $enableOptions) {
			$optionData = Option::parseEnableOptions($enableOptions);
			
			$options['disableOptions'][$key] = $optionData['disableOptions'];
			$options['enableOptions'][$key] = $optionData['enableOptions'];
		}
		
		WCF::getTPL()->assign(array(
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'selectOptions' => $selectOptions,
			'value' => $value
		));
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!empty($newValue)) {
			$options = $option->parseSelectOptions();
			if (!isset($options[$newValue])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * @see SearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		return $this->getFormElement($optionData, $value);
	}
	
	/**
	 * @see SearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		$value = StringUtil::trim($value);
		if (!$value) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", array($value));
		return true;
	}
}
?>
