<?php
namespace wcf\util;
use wcf\system\Regex;
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
	const CURRENCY_USD = "$";
	
	/**
	 * Formats an integer as a currency.
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
	 * Formats a flaot as a currency.
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
	
	/**
	 * Parses a currency string and returns it as an integer.
	 * 
	 * Accepted formats (using English as language):
	 * 	".2" -> 20
	 * 	".20" -> 20
	 * 	"5.2" -> 520
	 * 	"5.20" -> 520
	 * 	"520" -> 52000
	 * 	"52,052" -> 5205200
	 * 	"52052.05" -> 5205205
	 * 	"52,052.05" -> 5205205
	 * 
	 * @param	string	$currency
	 * @return	integer
	 */
	public static function parseInteger($currency) {
		$regex = '^[\d\\'.preg_quote(WCF::getLanguage()->get('wcf.global.thousandsSeparator')).']*';
		$regex .= '('.preg_quote(WCF::getLanguage()->get('wcf.global.decimalPoint')).'\d{0,2})?$';
		if (!Regex::compile($regex)->match($currency)) {
			throw new SystemException('"'.$currency.'" is no valid currency');
		}
		
		$integer = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '', $currency);
		$integer = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $integer);
		return instval($integer);
	}
	
	/**
	 * Parses a currency string and returns it as an float.
	 * See CurrencyUtil::parseInteger() for usage.
	 * 
	 * @param	string	$currency
	 * @return	float
	 */
	public static function paseFloat($currency) {
		return self::parseInteger($currency)/100;
	}
	
	private function __construct() { }
}