<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\template\TemplatePluginModifier;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * The 'time' modifier formats a unix timestamp.
 * Default date format contains year, month, day, hour and minute.
 * 
 * Usage:
 * {$timestamp|time}
 * {"132845333"|time}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierTime implements TemplatePluginModifier {
	/**
	 * @see wcf\system\template\TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$timestamp = intval($tagArgs[0]);
		$dateTimeObject = DateUtil::getDateTimeByTimestamp($timestamp);
		$date = DateUtil::format($dateTimeObject, DateUtil::DATE_FORMAT);
		$time = DateUtil::format($dateTimeObject, DateUtil::TIME_FORMAT);
		$dateTime = str_replace('%time%', $time, str_replace('%date%', $date, WCF::getLanguage()->get('wcf.global.date.dateTimeFormat')));
		
		return '<time datetime="'.DateUtil::format($dateTimeObject, 'c').'" class="datetime" data-timestamp="'.$timestamp.'" data-date="'.$date.'" data-time="'.$time.'" data-offset="'.$dateTimeObject->getOffset().'">'.$dateTime.'</time>';
	}
}
