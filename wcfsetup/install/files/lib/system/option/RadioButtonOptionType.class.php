<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Option type implementation for radio buttons.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class RadioButtonOptionType extends AbstractOptionType implements ISearchableConditionUserOption {
	/**
	 * name of the template that contains the form element of this option type
	 * @var	string
	 */
	public $templateName = 'radioButtonOptionType';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
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
			'selectOptions' => $this->getSelectOptions($option),
			'value' => $value
		));
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!empty($newValue)) {
			$options = $this->getSelectOptions($option);
			if (!isset($options[$newValue])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		$this->templateName = 'radioButtonSearchableOptionType';
		WCF::getTPL()->assign('searchOption', $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])));
		
		return $this->getFormElement($option, $value);
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (!isset($_POST['searchOptions'][$option->optionName])) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", array(StringUtil::trim($value)));
		return true;
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::addCondition()
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' = ?', array(StringUtil::trim($value)));
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::checkUser()
	 */
	public function checkUser(User $user, Option $option, $value) {
		return mb_strtolower($user->getUserOption($option->optionName)) == mb_strtolower(StringUtil::trim($value));
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::getConditionData()
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * Returns the select options for the given option.
	 * 
	 * @param	\wcf\dat\option\Option		$option
	 * @return	array<string>
	 */
	protected function getSelectOptions(Option $option) {
		return $option->parseSelectOptions();
	}
}
