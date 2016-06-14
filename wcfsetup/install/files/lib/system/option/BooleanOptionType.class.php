<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Option type implementation for boolean values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class BooleanOptionType extends AbstractOptionType implements ISearchableUserOption {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$options = Option::parseEnableOptions($option->enableOptions);
		
		WCF::getTPL()->assign([
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'value' => $value
		]);
		return WCF::getTPL()->fetch('booleanOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		if ($newValue == 1) return 1;
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchFormElement(Option $option, $value) {
		return $this->getFormElement($option, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		$value = intval($value);
		if (!$value) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", [1]);
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		$value = intval($value);
		if (!$value) return;
		
		$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' = ?', [1]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(User $user, Option $option, $value) {
		if (!$value) return false;
		
		return $user->getUserOption($option->optionName);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		return ($value1) ? 1 : -1;
	}
}
