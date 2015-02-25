<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template modifier plugin which formats a JSON string for usage in a single quoted
 * javascript string by escapes single quotes and new lines.
 * 
 * Usage:
 * 	{$string|encodeJSON}
 * 	{"bl''ah"|encodeJSON}
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class EncodeJSONModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return StringUtil::encodeJSON($tagArgs[0]);
	}
}
