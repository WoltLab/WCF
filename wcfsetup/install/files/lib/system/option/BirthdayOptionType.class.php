<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Option type implementation for birthday input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class BirthdayOptionType extends DateOptionType {
	/**
	 * @inheritDoc
	 */
	protected $inputClass = 'birthday';
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		parent::validate($option, $newValue);
		
		if (empty($newValue)) return;
		
		$timestamp = @strtotime($newValue);
		if ($timestamp > TIME_NOW || $timestamp < -2147483647) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		if ($value == '0000-00-00') $value = '';
		
		return parent::getFormElement($option, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchFormElement(Option $option, $value) {
		$ageFrom = $ageTo = '';
		if (!empty($value['ageFrom'])) $ageFrom = intval($value['ageFrom']);
		if (!empty($value['ageTo'])) $ageTo = intval($value['ageTo']);
		
		WCF::getTPL()->assign([
			'option' => $option,
			'valueAgeFrom' => $ageFrom,
			'valueAgeTo' => $ageTo
		]);
		return WCF::getTPL()->fetch('birthdaySearchableOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (empty($value['ageFrom']) && empty($value['ageTo'])) return false;
		
		$ageFrom = intval($value['ageFrom']);
		$ageTo = intval($value['ageTo']);
		if ($ageFrom < 0 || $ageFrom > 120) return false;
		if ($ageTo < 0 || $ageTo > 120) return false;
		
		$dateFrom = DateUtil::getDateTimeByTimestamp(TIME_NOW)->sub(new \DateInterval('P'.($ageTo + 1).'Y'))->add(new \DateInterval('P1D'));
		$dateTo = DateUtil::getDateTimeByTimestamp(TIME_NOW)->sub(new \DateInterval('P'.$ageFrom.'Y'));
		
		$conditions->add('option_value.userOption'.User::getUserOptionID('birthdayShowYear').' = ?', [1]);
		
		if ($ageFrom && $ageTo) {
			$conditions->add('option_value.userOption'.$option->optionID.' BETWEEN DATE(?) AND DATE(?)', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
		}
		else if ($ageFrom) {
			$conditions->add('option_value.userOption'.$option->optionID.' BETWEEN DATE(?) AND DATE(?)', ['1893-01-01', $dateTo->format('Y-m-d')]);
		}
		else {
			$conditions->add('option_value.userOption'.$option->optionID.' BETWEEN DATE(?) AND DATE(?)', [$dateFrom->format('Y-m-d'), DateUtil::getDateTimeByTimestamp(TIME_NOW)->add(new \DateInterval('P1D'))->format('Y-m-d')]);
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		$ageFrom = intval($value['ageFrom']);
		$ageTo = intval($value['ageTo']);
		
		if ($ageFrom < 0 || $ageFrom > 120 || $ageTo < 0 || $ageTo > 120) return false;
		
		$dateFrom = DateUtil::getDateTimeByTimestamp(TIME_NOW)->sub(new \DateInterval('P'.($ageTo + 1).'Y'))->add(new \DateInterval('P1D'));
		$dateTo = DateUtil::getDateTimeByTimestamp(TIME_NOW)->sub(new \DateInterval('P'.$ageFrom.'Y'));
		
		$userList->getConditionBuilder()->add('user_option_value.userOption'.User::getUserOptionID('birthdayShowYear').' = ?', [1]);
		
		if ($ageFrom && $ageTo) {
			$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' BETWEEN DATE(?) AND DATE(?)', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
		}
		else if ($ageFrom) {
			$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' BETWEEN DATE(?) AND DATE(?)', ['1893-01-01', $dateTo->format('Y-m-d')]);
		}
		else {
			$userList->getConditionBuilder()->add('user_option_value.userOption'.$option->optionID.' BETWEEN DATE(?) AND DATE(?)', [$dateFrom->format('Y-m-d'), DateUtil::getDateTimeByTimestamp(TIME_NOW)->add(new \DateInterval('P1D'))->format('Y-m-d')]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(User $user, Option $option, $value) {
		if (!$user->birthdayShowYear || !$user->birthday) return false;
		
		$ageFrom = intval($value['ageFrom']);
		$ageTo = intval($value['ageTo']);
		
		$userAge = DateUtil::getAge($user->birthday);
		
		if ($ageFrom && $ageTo) {
			return $userAge >= $ageFrom && $userAge <= $ageTo;
		}
		else if ($ageFrom) {
			return $userAge >= $ageFrom;
		}
		else {
			return $userAge <= $ageTo;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionData(Option $option, $newValue) {
		if (!$newValue['ageFrom'] && !$newValue['ageTo']) return null;
		
		return $newValue;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hideLabelInSearch() {
		return false;
	}
}
