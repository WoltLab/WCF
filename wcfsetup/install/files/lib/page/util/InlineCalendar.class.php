<?php
namespace wcf\page\util;
use wcf\data\language\Language;
use wcf\util\DateUtil;
use wcf\system\WCF;

/**
 * Assigns default variables for the usage of the inline (javascript) calendar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page.util
 * @category 	Community Framework
 */
class InlineCalendar {
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public static function assignVariables() {
		// create calendar data
		$dayOptions = array(0 => '');
		$hourOptions = $minuteOptions = array('' => '');
		$monthList = $weekdayList = '';
		$weekdays = $monthOptions = array();
		
		// days
		for ($i = 1; $i <= 31; $i++) $dayOptions[$i] = $i;
		// months
		$monthFormat = (Language::$dateFormatLocalized ? '%B' : '%m');
		for ($i = 1; $i <= 12; $i++) $monthOptions[$i] = DateUtil::formatDate($monthFormat, gmmktime(0, 0, 0, $i, 15, 2000), false, true);
		$monthList = implode(',', $monthOptions);
		$monthOptions[0] = '';
		ksort($monthOptions);
		
		// weekdays
		for ($i = 1; $i <= 7; $i++) {
			$weekdayNumber = intval(DateUtil::formatDate('%w', gmmktime(0, 0, 0, 1, $i, 2000), false, true));
			$weekdays[$weekdayNumber] = DateUtil::formatDate('%a', gmmktime(0, 0, 0, 1, $i, 2000), false, true);
		}
		ksort($weekdays);
		$weekdayList = implode(',', $weekdays);
		
		// hours
		for ($i = 0; $i < 24; $i++) $hourOptions[$i] = $i < 10 ? "0" . $i : $i;
		
		// minutes
		for ($i = 0; $i < 60; $i += 5) $minuteOptions[$i] = $i < 10 ? "0" . $i : $i;
		
		WCF::getTPL()->assign(array(
			'monthOptions' => $monthOptions,
			'monthList' => $monthList,
			'dayOptions' => $dayOptions,
			'weekdayList' => $weekdayList,
			'startOfWeek' => (WCF::getUser()->firstDayOfWeek == '' ? 1 : intval(WCF::getUser()->firstDayOfWeek)),
			'hourOptions' => $hourOptions,
			'minuteOptions' => $minuteOptions
		));
	}
}
