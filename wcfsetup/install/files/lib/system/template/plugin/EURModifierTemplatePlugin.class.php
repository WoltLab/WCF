<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\CurrencyUtil;

/**
 * Template modifier plugin which formats an integer as a currency.
 * 
 * Usage (with English as language):
 * 	{520|currency} -> 5.20 €
 * 	{35181321684351384|currency} -> 351,813,216,843,513.84 €
 * 
 * @author	Magnus Kühn
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class EURModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	wcf\system\template\ITemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return CurrencyUtil::formatFloat(intval($tagArgs[0]), CurrencyUtil::CURRENCY_EUR);
	}
}
