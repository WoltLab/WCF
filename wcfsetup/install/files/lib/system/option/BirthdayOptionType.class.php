<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Option type implementation for birthday input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class BirthdayOptionType extends DateOptionType {
	/**
	 * input css class
	 * @var	string
	 */
	protected $inputClass = 'birthday';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function validate(Option $option, $newValue) {
		parent::validate($option, $newValue);
		
		if (empty($newValue)) return;
		
		$timestamp = @strtotime($newValue); 
		if ($timestamp > TIME_NOW) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		if ($value == '0000-00-00') $value = '';
		
		return parent::getFormElement($option, $value);
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		$ageFrom = $ageTo = '';
		if (!empty($value['ageFrom'])) $ageFrom = intval($value['ageFrom']);
		if (!empty($value['ageTo'])) $ageTo = intval($value['ageTo']);
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'valueAgeFrom' => $ageFrom,
			'valueAgeTo' => $ageTo
		));
		return WCF::getTPL()->fetch('birthdaySearchableOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		if (empty($value['ageFrom']) || empty($value['ageTo'])) return false;
		
		$ageFrom = intval($value['ageFrom']);
		$ageTo = intval($value['ageTo']);
		if ($ageFrom < 0 || $ageFrom > 120) return false;
		if ($ageTo < 0 || $ageTo > 120) return false;
		if (!$ageFrom || !$ageTo) return false;
		
		$dateFrom = DateUtil::getDateTimeByTimestamp(TIME_NOW)->sub(new \DateInterval('P'.($ageTo + 1).'Y'))->add(new \DateInterval('P1D'));
		$dateTo = DateUtil::getDateTimeByTimestamp(TIME_NOW)->sub(new \DateInterval('P'.$ageFrom.'Y'));
		
		$conditions->add("option_value.userOption".User::getUserOptionID('birthdayShowYear')." = ? AND option_value.userOption".$option->optionID." BETWEEN DATE(?) AND DATE(?)", array(1, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')));
		return true;
	}
}
