<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Option type implementation for multiple select lists.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class MultiSelectOptionType extends SelectOptionType {
	/**
	 * name of the form element template
	 * @var	string
	 */
	protected $formElementTemplate = 'multiSelectOptionType';
	
	/**
	 * name of the searchable form element template
	 * @var	string
	 */
	protected $searchableFormElementTemplate = 'multiSelectSearchableOptionType';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'selectOptions' => $this->getSelectOptions($option),
			'value' => (!is_array($value) ? explode("\n", $value) : $value)
		));
		return WCF::getTPL()->fetch($this->formElementTemplate);
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'searchOption' => $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])),
			'selectOptions' => $this->getSelectOptions($option),
			'value' => (!is_array($value) ? explode("\n", $value) : $value)
		));
		return WCF::getTPL()->fetch($this->searchableFormElementTemplate);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$options = $this->getSelectOptions($option);
		foreach ($newValue as $value) {
			if (!isset($options[$value])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		return implode("\n", $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (!isset($_POST['searchOptions'][$option->optionName])) return false;
		
		if (!is_array($value) || empty($value)) return false;
		$value = ArrayUtil::trim($value);
		
		$conditions->add("option_value.userOption".$option->optionID." REGEXP '".'(^|\n)'.implode('\n([^\n]*\n)*', array_map('escapeString', $value)).'($|\n)'."'");
		return true;
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::addCondition()
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		if (!is_array($value) || empty($value)) return false;
		$value = ArrayUtil::trim($value);
		
		$userList->getConditionBuilder()->add("user_option_value.userOption".$option->optionID." REGEXP '".'(^|\n)'.implode('\n([^\n]*\n)*', array_map('escapeString', $value)).'($|\n)'."'");
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::checkUser()
	 */
	public function checkUser(User $user, Option $option, $value) {
		if (!is_array($value) || empty($value)) return false;
		
		$optionValues = explode('\n', $user->getUserOption($option->optionName));
		
		return count(array_diff($optionValues, $value)) > 0;
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::getConditionData()
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
}
