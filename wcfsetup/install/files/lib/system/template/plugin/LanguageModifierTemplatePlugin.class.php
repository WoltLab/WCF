<?php
namespace wcf\system\template\plugin;
use wcf\system\WCF;
use wcf\system\template\TemplateEngine;

/**
 * The 'language' modifier returns dynamic language variables.
 * 
 * Usage:
 * {$string|language}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class LanguageModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return WCF::getLanguage()->getDynamicVariable($tagArgs[0]);
	}
}
