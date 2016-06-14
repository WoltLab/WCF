<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template modifier plugin which truncates a string.
 * 
 * Usage:
 * 	{$foo|truncate:35:' and more'}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class TruncateModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// default values
		$length = 80;
		$etc = StringUtil::HELLIP;
		$breakWords = false;
		
		// get values
		$string = $tagArgs[0];
		if (isset($tagArgs[1])) $length = intval($tagArgs[1]);
		if (isset($tagArgs[2])) $etc = $tagArgs[2];
		if (isset($tagArgs[3])) $breakWords = $tagArgs[3];
		
		return StringUtil::truncate($string, $length, $etc, $breakWords);
	}
}
