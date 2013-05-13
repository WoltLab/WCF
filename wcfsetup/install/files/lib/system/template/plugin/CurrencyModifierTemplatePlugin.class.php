<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template modifier plugin which formats a float as a currency.
 * 
 * Usage (with English as language):
 * 	{5.2|currency} -> 5.20 €
 * 	{7612.59|currency:USD} -> 7,612.59 $
 * 	{51248637955248|currency:"FNJHB":true} -> FNJHB 51,248,637,955,248
 * 
 * @author	Magnus Kühn
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class CurrencyModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	wcf\system\template\ITemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$currency = StringUtil::CURRENCY_EUR;
		if (!empty($tagArgs[1])) {
			$currency = $tagArgs[1];
			
			$constant = 'CURRENCY_'.StringUtil::toUpperCase($currency);
			if (defined('wcf\util\StringUtil::'.$constant)) {
				$currency = constant('wcf\util\StringUtil::'.$constant);
			}
		}
		
		return StringUtil::formatFloat(floatval($tagArgs[0]), $currency, (isset($tagArgs[2]) ? (boolean) $tagArgs[2] : false));
	}
}
