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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class LanguageModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (($lang = $tplObj->get('__language')) === null) {
			$lang = WCF::getLanguage();
		}
		
		return $lang->getDynamicVariable($tagArgs[0]);
	}
}
