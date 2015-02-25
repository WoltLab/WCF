<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template modifier plugin which returns dynamic language variables.
 * 
 * Usage:
 * 	{$string|language}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class LanguageModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (($lang = $tplObj->get('__language')) === null) {
			$lang = WCF::getLanguage();
		}
		
		return $lang->getDynamicVariable($tagArgs[0]);
	}
}
