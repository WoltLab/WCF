<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Template modifier plugin which formats a unix timestamp.
 * Default date format contains year, month, day, hour and minute.
 * 
 * Usage:
 * 	{$timestamp|time}
 * 	{"132845333"|time}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
		$dateTime = DateUtil::getRelativeTime($dateTimeObject, $timestamp, $date, $time, $isFutureDate);
		
		return '<time datetime="'.DateUtil::format($dateTimeObject, 'c').'" class="datetime" data-timestamp="'.$timestamp.'" data-date="'.StringUtil::encodeHTML($date).'" data-time="'.StringUtil::encodeHTML($time).'" data-offset="'.$dateTimeObject->getOffset().'"'.($isFutureDate ? ' data-is-future-date="true"' : '').'>'.$dateTime.'</time>';
	}
}
