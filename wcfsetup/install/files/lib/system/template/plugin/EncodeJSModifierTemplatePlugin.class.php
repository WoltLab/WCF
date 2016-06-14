<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template modifier plugin which formats a string for usage in a single quoted
 * javascript string by escapes single quotes and new lines.
 * 
 * Usage:
 * 	{$string|encodeJS}
 * 	{"bl''ah"|encodeJS}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class EncodeJSModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return StringUtil::encodeJS($tagArgs[0]);
	}
}
