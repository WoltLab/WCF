<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * The 'newlineToBreak' modifier inserts HTML line breaks before all newlines.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class NewlineToBreakModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return nl2br(StringUtil::encodeHTML($tagArgs[0]));
	}
}
