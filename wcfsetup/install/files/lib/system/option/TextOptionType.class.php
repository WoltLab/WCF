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
 * Option type implementation for textual input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class TextOptionType extends AbstractOptionType implements ISearchableConditionUserOption {
	/**
	 * input type
	 * @var	string
	 */
	protected $inputType = 'text';
	
	/**
	 * input css class
	 * @var	string
	 */
	protected $inputClass = 'long';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'inputType' => $this->inputType,
			'inputClass' => $this->inputClass,
			'value' => $value
		));
		return WCF::getTPL()->fetch('textOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'inputType' => $this->inputType,
			'inputClass' => $this->inputClass,
			'searchOption' => $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])),
			'value' => $value
		));
		return WCF::getTPL()->fetch('textSearchableOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (!isset($_POST['searchOptions'][$option->optionName])) return false;
		
		$value = StringUtil::trim($value);
		if ($value == '') {
			$conditions->add("option_value.userOption".$option->optionID." = ?", array(''));
		}
		else {
			$conditions->add("option_value.userOption".$option->optionID." LIKE ?", array('%'.addcslashes($value, '_%').'%'));
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		$newValue = $this->getContent($option, $newValue);
		
		if ($option->minlength !== null && $option->minlength > mb_strlen($newValue)) {
			throw new UserInputException($option->optionName, 'tooShort');
		}
		if ($option->maxlength !== null && $option->maxlength < mb_strlen($newValue)) {
			throw new UserInputException($option->optionName, 'tooLong');
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return $this->getContent($option, $newValue);
	}
	
	/**
	 * Tries to extract content from value.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 * @param	string				$newValue
	 * @return	string
	 */
	protected function getContent(Option $option, $newValue) {
		if ($option->contentpattern) {
			if (preg_match('~'.$option->contentpattern.'~', $newValue, $matches)) {
				unset($matches[0]);
				$newValue = implode('', $matches);
			}
		}
		
		return $newValue;
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::addCondition()
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		$value = StringUtil::trim($value);
		if ($value == '') {
			$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' = ?', array(''));
		}
		else {
			$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' LIKE ?', array('%'.addcslashes($value, '_%').'%'));
		}
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::checkUser()
	 */
	public function checkUser(User $user, Option $option, $value) {
		$value = StringUtil::trim($value);
		if ($value == '') {
			return $user->getUserOption($option->optionName) == '';
		}
		else {
			return mb_stripos($user->getUserOption($option->optionName), $value) !== false;
		}
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::getConditionData()
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::hideLabelInSearch()
	 */
	public function hideLabelInSearch() {
		return true;
	}
}
