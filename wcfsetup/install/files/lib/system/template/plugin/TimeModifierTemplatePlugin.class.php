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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class TimeModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$timestamp = intval($tagArgs[0]);
		$dateTimeObject = DateUtil::getDateTimeByTimestamp($timestamp);
		$date = DateUtil::format($dateTimeObject, DateUtil::DATE_FORMAT);
		$time = DateUtil::format($dateTimeObject, DateUtil::TIME_FORMAT);
		$dateTime = str_replace('%time%', $time, str_replace('%date%', $date, WCF::getLanguage()->get('wcf.date.dateTimeFormat')));
		
		return '<time datetime="'.DateUtil::format($dateTimeObject, 'c').'" class="datetime" data-timestamp="'.$timestamp.'" data-date="'.$date.'" data-time="'.$time.'" data-offset="'.$dateTimeObject->getOffset().'"'.($timestamp > TIME_NOW ? ' data-is-future-date="true"' : '').'>'.$dateTime.'</time>';
	}
}
