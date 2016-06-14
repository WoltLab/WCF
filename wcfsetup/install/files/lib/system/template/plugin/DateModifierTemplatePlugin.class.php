<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;

/**
 * Template modifier plugin which formats a unix timestamp.
 * The default date format contains year, month and day.
 * 
 * Usage:
 * 	{$timestamp|date}
 * 	{"132845333"|date:"Y-m-d"}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class DateModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return DateUtil::format(DateUtil::getDateTimeByTimestamp($tagArgs[0]), (!empty($tagArgs[1]) ? $tagArgs[1] : DateUtil::DATE_FORMAT));
	}
}
