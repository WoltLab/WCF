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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class RadioButtonOptionType extends AbstractOptionType implements ISearchableConditionUserOption {
	/**
	 * name of the template that contains the form element of this option type
	 * @var	string
	 */
	public $templateName = 'radioButtonOptionType';
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$availableOptions = $option->parseMultipleEnableOptions();
		$options = [
			'disableOptions' => [],
			'enableOptions' => []
		];
		
		foreach ($availableOptions as $key => $enableOptions) {
			$optionData = Option::parseEnableOptions($enableOptions);
			
			$options['disableOptions'][$key] = $optionData['disableOptions'];
			$options['enableOptions'][$key] = $optionData['enableOptions'];
		}
		
		WCF::getTPL()->assign([
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'selectOptions' => $this->getSelectOptions($option),
			'value' => $value
		]);
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function getSearchFormElement(Option $option, $value) {
		$this->templateName = 'radioButtonSearchableOptionType';
		WCF::getTPL()->assign('searchOption', $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])));
		
		return $this->getFormElement($option, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (!isset($_POST['searchOptions'][$option->optionName])) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", [StringUtil::trim($value)]);
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' = ?', [StringUtil::trim($value)]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(User $user, Option $option, $value) {
		return mb_strtolower($user->getUserOption($option->optionName)) == mb_strtolower(StringUtil::trim($value));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * Returns the select options for the given option.
	 * 
	 * @param	Option		$option
	 * @return	string[]
	 */
	protected function getSelectOptions(Option $option) {
		return $option->parseSelectOptions();
	}
}
