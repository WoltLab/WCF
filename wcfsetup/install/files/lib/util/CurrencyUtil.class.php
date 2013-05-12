<?php
namespace wcf\util;
use wcf\system\WCF;

/**
 * Contains currency-related functions.
 * 
 * @author	Magnus Kühn
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class CurrencyUtil {
	const CURRENCY_EUR = "€";
	
	/**
	 * Formats an integer as a currency
	 * 
	 * @param	integer	$integer
	 * @param	string	$currency
	 * @param	boolean	$prependCurrency
	 * @return	string
	 */
	public static function formatInteger($integer, $currency = self::CURRENCY_EUR, $prependCurrency = false) {
		return self::formatFloat($integer/100, $currency, $prependCurrency);
	}
	
	/**
	 * Formats a flaot as a currency
	 * 
	 * @param	float	$float
	 * @param	string	$currency
	 * @param	boolean	$prependCurrency
	 * @return	string
	 */
	public static function formatFloat($float, $currency = self::CURRENCY_EUR, $prependCurrency = false) {
		$formatted = StringUtil::formatNegative(number_format($float/100, 2, WCF::getLanguage()->get('wcf.global.decimalPoint'), WCF::getLanguage()->get('wcf.global.thousandsSeparator')));
		return ($prependCurrency ? $currency.' '.$formatted : $formatted.' '.$currency);
	}
	
	private function __construct() { }
}