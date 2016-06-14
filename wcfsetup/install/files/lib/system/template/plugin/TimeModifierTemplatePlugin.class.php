<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Template modifier plugin which formats a unix timestamp.
 * Default date format contains year, month, day, hour and minute.
 * 
 * Usage:
 * 	{$timestamp|time}
 * 	{"132845333"|time}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class TimeModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$timestamp = intval($tagArgs[0]);
		$dateTimeObject = DateUtil::getDateTimeByTimestamp($timestamp);
		$date = DateUtil::format($dateTimeObject, DateUtil::DATE_FORMAT);
		$time = DateUtil::format($dateTimeObject, DateUtil::TIME_FORMAT);
		$isFutureDate = ($timestamp > TIME_NOW);
		$dateTime = $this->getRelativeTime($dateTimeObject, $timestamp, $date, $time, $isFutureDate);
		
		return '<time datetime="'.DateUtil::format($dateTimeObject, 'c').'" class="datetime" data-timestamp="'.$timestamp.'" data-date="'.$date.'" data-time="'.$time.'" data-offset="'.$dateTimeObject->getOffset().'"'.($isFutureDate ? ' data-is-future-date="true"' : '').'>'.$dateTime.'</time>';
	}
	
	/**
	 * Returns the relative date time identical to the relative time generated
	 * through JavaScript.
	 * 
	 * @param	\DateTime	$dateTimeObject		target date object
	 * @param	integer		$timestamp		target timestamp
	 * @param	string		$date			localized date
	 * @param	string		$time			localized time
	 * @param	boolean		$isFutureDate		true if timestamp is in the future
	 * @return	string		relative time
	 */
	protected function getRelativeTime(\DateTime $dateTimeObject, $timestamp, $date, $time, $isFutureDate) {
		if ($isFutureDate) {
			return str_replace('%time%', $time, str_replace('%date%', $date, WCF::getLanguage()->get('wcf.date.dateTimeFormat')));
		}
		
		// timestamp is less than 60 seconds ago
		if ($timestamp >= TIME_NOW || TIME_NOW < ($timestamp + 60)) {
			return WCF::getLanguage()->get('wcf.date.relative.now');
		}
		// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
		else if (TIME_NOW < ($timestamp + 3540)) {
			$minutes = max(round((TIME_NOW - $timestamp) / 60), 1);
			
			return WCF::getLanguage()->getDynamicVariable('wcf.date.relative.minutes', ['minutes' => $minutes]);
		}
		// timestamp is less than 24 hours ago
		else if (TIME_NOW < ($timestamp + 86400)) {
			$hours = round((TIME_NOW - $timestamp) / 3600);
			
			return WCF::getLanguage()->getDynamicVariable('wcf.date.relative.hours', ['hours' => $hours]);
		}
		// timestamp is less than 6 days ago
		else if (TIME_NOW < ($timestamp + 518400)) {
			$dtoNoTime = clone $dateTimeObject;
			$dtoNoTime->setTime(0, 0, 0);
			$currentDateTimeObject = DateUtil::getDateTimeByTimestamp(TIME_NOW);
			$currentDateTimeObject->setTime(0, 0, 0);
			
			$days = $dtoNoTime->diff($currentDateTimeObject)->days;
			$day = DateUtil::format($dateTimeObject, 'l');
			
			return WCF::getLanguage()->getDynamicVariable('wcf.date.relative.pastDays', [
				'days' => $days,
				'day' => $day,
				'time' => $time
			]);
		}
		
		// timestamp is between ~700 million years BC and last week
		$datetime = WCF::getLanguage()->get('wcf.date.shortDateTimeFormat');
		$datetime = str_replace('%date%', $date, $datetime);
		$datetime = str_replace('%time%', $time, $datetime);
		
		return $datetime;
	}
}
