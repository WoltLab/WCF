<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Option type implementation for radio buttons.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class RadioButtonOptionType extends AbstractOptionType implements ISearchableUserOption {
	/**
	 * name of the template that contains the form element of this option type
	 * @var	string
	 */
	public $templateName = 'radioButtonOptionType';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
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
	 * @see	\wcf\system\option\IOptionType::validate()
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
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		return $this->getFormElement($option, $value);
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		$value = StringUtil::trim($value);
		if (!$value) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", array($value));
		return true;
	}
}
