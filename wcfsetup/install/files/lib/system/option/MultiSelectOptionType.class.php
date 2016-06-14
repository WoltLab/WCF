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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
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
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign([
			'option' => $option,
			'selectOptions' => $this->getSelectOptions($option),
			'value' => (!is_array($value) ? explode("\n", $value) : $value)
		]);
		return WCF::getTPL()->fetch($this->formElementTemplate);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchFormElement(Option $option, $value) {
		WCF::getTPL()->assign([
			'option' => $option,
			'searchOption' => $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])),
			'selectOptions' => $this->getSelectOptions($option),
			'value' => (!is_array($value) ? explode("\n", $value) : $value)
		]);
		return WCF::getTPL()->fetch($this->searchableFormElementTemplate);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		$options = $this->getSelectOptions($option);
		foreach ($newValue as $value) {
			if (!isset($options[$value])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		return implode("\n", $newValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (!isset($_POST['searchOptions'][$option->optionName])) return false;
		
		if (!is_array($value) || empty($value)) return false;
		$value = ArrayUtil::trim($value);
		
		$conditions->add("option_value.userOption".$option->optionID." REGEXP '".'(^|\n)'.implode('\n([^\n]*\n)*', array_map('escapeString', $value)).'($|\n)'."'");
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		if (!is_array($value) || empty($value)) return false;
		$value = ArrayUtil::trim($value);
		
		$userList->getConditionBuilder()->add("user_option_value.userOption".$option->optionID." REGEXP '".'(^|\n)'.implode('\n([^\n]*\n)*', array_map('escapeString', $value)).'($|\n)'."'");
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(User $user, Option $option, $value) {
		if (!is_array($value) || empty($value)) return false;
		
		$optionValues = explode('\n', $user->getUserOption($option->optionName));
		
		return count(array_diff($optionValues, $value)) > 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
}
