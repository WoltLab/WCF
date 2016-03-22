<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Shortens numbers larger than 1000 by using unit prefixes.
 *
 * Usage:
 * 	{12345|shortUnit}
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class ShortUnitModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$number = $tagArgs[0];
		$unitPrefix = '';
		
		if ($number >= 1000000) {
			$number /= 1000000;
			if ($number > 10) {
				$number = floor($number);
			}
			else {
				$number = round($number, 1);
			}
			$unitPrefix = 'M';
		}
		else if ($number >= 1000) {
			$number /= 1000;
			if ($number > 10) {
				$number = floor($number);
			}
			else {
				$number = round($number, 1);
			}
			$unitPrefix = 'k';
		}
		
		return StringUtil::formatNumeric($number) . $unitPrefix;
	}
}
