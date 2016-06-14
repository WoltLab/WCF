<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\DateUtil;

/**
 * User option output implementation for for the output of a date input.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
class DateUserOptionOutput implements IUserOptionOutput {
	/**
	 * date format
	 * @var	string
	 */
	protected $dateFormat = DateUtil::DATE_FORMAT;
	
	/**
	 * @inheritDoc
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		if (empty($value) || $value == '0000-00-00') return '';
		
		$date = self::splitDate($value);
		return DateUtil::format(DateUtil::getDateTimeByTimestamp(gmmktime(12, 1, 1, $date['month'], $date['day'], $date['year'])), $this->dateFormat);
	}
	
	/**
	 * Splits the given dashed date into its components.
	 * 
	 * @param	string		$value
	 * @return	integer[]
	 */
	protected static function splitDate($value) {
		$year = $month = $day = 0;
		$optionValue = explode('-', $value);
		if (isset($optionValue[0])) $year = intval($optionValue[0]);
		if (isset($optionValue[1])) $month = intval($optionValue[1]);
		if (isset($optionValue[2])) $day = intval($optionValue[2]);
		
		return ['year' => $year, 'month' => $month, 'day' => $day];
	}
}
