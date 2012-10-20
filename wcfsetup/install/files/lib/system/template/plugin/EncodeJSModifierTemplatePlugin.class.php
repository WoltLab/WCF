<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * The 'encodeJS' modifier formats a string for usage in a single quoted javascript string. 
 * Escapes single quotes and new lines.
 * 
 * Usage:
 *	{$string|encodeJS}
 *	{"bl''ah"|encodeJS}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class EncodeJSModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return StringUtil::encodeJS($tagArgs[0]);
	}
}
