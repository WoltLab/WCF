<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\template\ITemplatePluginModifier;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * The 'plainTime' modifier formats a unix timestamp.
 * Default date format contains year, month, day, hour and minute.
 * 
 * Usage:
 * {$timestamp|plainTime}
 * {"132845333"|plainTime}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierPlainTime implements ITemplatePluginModifier {
	/**
	 * @see wcf\system\template\ITemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$dateTime = DateUtil::getDateTimeByTimestamp($tagArgs[0]);
		return str_replace('%time%', DateUtil::format($dateTime, DateUtil::TIME_FORMAT), str_replace('%date%', DateUtil::format($dateTime, DateUtil::DATE_FORMAT), WCF::getLanguage()->get('wcf.global.date.dateTimeFormat')));
	}
}
