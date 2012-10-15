<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;

/**
 * The 'date' modifier formats a unix timestamp. The default date format contains
 * year, month and day.
 * 
 * Usage:
 *	{$timestamp|date}
 *	{"132845333"|date:"Y-m-d"}
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class DateModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return DateUtil::format(DateUtil::getDateTimeByTimestamp($tagArgs[0]), (!empty($tagArgs[2]) ? $tagArgs[2] : DateUtil::DATE_FORMAT));
	}
}
