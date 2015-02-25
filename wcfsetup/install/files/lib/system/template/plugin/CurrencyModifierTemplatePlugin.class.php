<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Formats a currency value.
 * 
 * Usage:
 * 	{$float|currency}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class CurrencyModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return number_format(round($tagArgs[0], 2), 2, WCF::getLanguage()->get('wcf.global.decimalPoint'), WCF::getLanguage()->get('wcf.global.thousandsSeparator'));
	}
}
