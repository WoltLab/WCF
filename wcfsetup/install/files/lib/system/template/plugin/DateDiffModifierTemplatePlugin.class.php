<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;

/**
 * Template modifier plugin which calculates the difference between two unix timestamps
 * and returns it as a textual date interval. The second parameter $fullInterval
 * indicates if the full difference is returned or just a rounded difference.
 * 
 * Usage:
 *	{$timestamp|dateDiff}
 *	{"123456789"|dateDiff:$timestamp:$fullInverval}
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class DateDiffModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (!isset($tagArgs[1])) {
			$tagArgs[1] = TIME_NOW;
		}
		
		$fullInterval = false;
		if (isset($tagArgs[2])) {
			$fullInterval = $tagArgs[2];
		}
		
		$startTime = DateUtil::getDateTimeByTimestamp($tagArgs[1]);
		$endTime = DateUtil::getDateTimeByTimestamp($tagArgs[0]);
		
		return DateUtil::formatInterval($endTime->diff($startTime), $fullInterval);
	}
}
