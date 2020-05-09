<?php /** @noinspection ALL */
namespace wcf\system\language;
use wcf\system\WCF;

/**
 * Provides functions for pluralization rules.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Language
 * @since	5.3
 * @see https://unicode.org/cldr/charts/latest/supplemental/language_plural_rules.html
 */
class I18nPlural {
	const PLURAL_FEW = 'few';
	const PLURAL_MANY = 'many';
	const PLURAL_ONE = 'one';
	const PLURAL_OTHER = 'other';
	const PLURAL_TWO = 'two';
	const PLURAL_ZERO = 'zero';
	
	/**
	 * Returns the plural category for the given value.
	 * 
	 * @param       number         $value
	 * @param       string         $languageCode
	 * @return      string
	 */
	public static function getCategory($value, $languageCode = null) {
		if ($languageCode === null) {
			$languageCode = WCF::getLanguage()->getFixedLanguageCode();
		}
		
		// Fallback: handle unknown languages as English
		if (!method_exists(self::class, $languageCode)) {
			$languageCode = 'en';
		}
		
		if ($category = self::$languageCode($value)) {
			return $category;
		}
		
		return self::PLURAL_OTHER;
	}
	
	/**
	 * `f` is the fractional number as a whole number (1.234 yields 234)
	 *
	 * @param       number          $n
	 * @return      integer
	 */
	private static function getF($n) {
		$n = (string)$n;
		$pos = strpos($n, '.');
		if ($pos === false) {
			return 0;
		}
		
		return intval(substr($n, $pos + 1));
	}
	
	/**
	 * `v` represents the number of digits of the fractional part (1.234 yields 3)
	 *
	 * @param       number          $n
	 * @return      integer
	 */
	private static function getV($n) {
		return strlen(preg_replace('/^[^.]*\.?/', '', (string)$n));
	}
	
	// Afrikaans
	private static function af($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Amharic
	private static function am($n) {
		$i = floor(abs($n));
		if ($n == 1 || $i === 0) return self::PLURAL_ONE;
	}
	
	// Arabic
	private static function ar($n) {
		if ($n == 0) return self::PLURAL_ZERO;
		if ($n == 1) return self::PLURAL_ONE;
		if ($n == 2) return self::PLURAL_TWO;
		
		$mod100 = $n % 100;
		if ($mod100 >= 3 && $mod100 <= 10) return self::PLURAL_FEW;
		if ($mod100 >= 11 && $mod100 <= 99) return self::PLURAL_MANY;
	}
	
	// Assamese
	private static function as($n) {
		$i = floor(abs($n));
		if ($n == 1 || $i === 0) return self::PLURAL_ONE;
	}
	
	// Azerbaijani
	private static function az($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Belarusian
	private static function be($n) {
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		
		if ($mod10 == 1 && $mod100 != 11) return self::PLURAL_ONE;
		if ($mod10 >= 2 && $mod10 <= 4 && !($mod100 >= 12 && $mod100 <= 14)) return self::PLURAL_FEW;
		if ($mod10 == 0 || ($mod10 >= 5 && $mod10 <= 9) || ($mod100 >= 11 && $mod100 <= 14)) return self::PLURAL_MANY;
	}
	
	// Bulgarian
	private static function bg($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Bengali
	private static function bn($n) {
		$i = floor(abs($n));
		if ($n == 1 || $i === 0) return self::PLURAL_ONE;
	}
	
	// Tibetan
	private static function bo($n) {}
	
	// Bosnian
	private static function bs($n) {
		$v = self::getV($n);
		$f = self::getF($n);
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		$fMod10 = $f % 10;
		$fMod100 = $f % 100;
		
		if (($v == 0 && $mod10 == 1 && $mod100 != 11) || ($fMod10 == 1 && $fMod100 != 11)) return self::PLURAL_ONE;
		if (($v == 0 && $mod10 >= 2 && $mod10 <= 4 && $mod100 >= 12 && $mod100 <= 14)
			|| ($fMod10 >= 2 && $fMod10 <= 4 && $fMod100 >= 12 && $fMod100 <= 14)) return self::PLURAL_FEW;
	}
		
	// Czech
	private static function cs($n) {
		$v = self::getV($n);
		
		if ($n == 1 && $v === 0) return self::PLURAL_ONE;
		if ($n >= 2 && $n <= 4 && $v === 0) return self::PLURAL_FEW;
		if ($v === 0) return self::PLURAL_MANY;
	}
	
	// Welsh
	private static function cy($n) {
		if ($n == 0) return self::PLURAL_ZERO;
		if ($n == 1) return self::PLURAL_ONE;
		if ($n == 2) return self::PLURAL_TWO;
		if ($n == 3) return self::PLURAL_FEW;
		if ($n == 6) return self::PLURAL_MANY;
	}
	
	// Danish
	private static function da($n) {
		if ($n > 0 && $n < 2) return self::PLURAL_ONE;
	}
	
	// Greek
	private static function el($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}

	// Catalan (ca)
	// German (de)
	// English (en)
	// Estonian (et)
	// Finnish (fi)
	// Italian (it)
	// Dutch (nl)
	// Swedish (sv)
	// Swahili (sw)
	// Urdu (ur)
	private static function en($n) {
		if ($n == 1 && self::getV($n) === 0) return self::PLURAL_ONE;
	}
	
	// Spanish
	private static function es($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Basque
	private static function eu($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Persian
	private static function fa($n) {
		if ($n >= 0 && $n <= 1) return self::PLURAL_ONE;
	}
	
	// French
	private static function fr($n) {
		if ($n >= 0 && $n < 2) return self::PLURAL_ONE;
	}
	
	// Irish
	private static function ga($n) {
		if ($n == 1) return self::PLURAL_ONE;
		if ($n == 2) return self::PLURAL_TWO;
		if ($n == 3 || $n == 4 || $n == 5 || $n == 6) return self::PLURAL_FEW;
		if ($n == 7 || $n == 8 || $n == 9 || $n == 10) return self::PLURAL_MANY;
	}
	
	// Gujarati
	private static function gu($n) {
		if ($n >= 0 && $n <= 1) return self::PLURAL_ONE;
	}
	
	// Hebrew
	private static function he($n) {
		$v = self::getV($n);
		
		if ($n == 1 && $v === 0) return self::PLURAL_ONE;
		if ($n == 2 && $v === 0) return self::PLURAL_TWO;
		if ($n > 10 && $v === 0 && $n % 10 == 0) return self::PLURAL_MANY;
	}
	
	// Hindi
	private static function hi($n) {
		if ($n >= 0 && $n <= 1) return self::PLURAL_ONE;
	}
	
	// Croatian
	private static function hr($n) {
		// same as Bosnian
		return self::bs($n);
	}
	
	// Hungarian
	private static function hu($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Armenian
	private static function hy($n) {
		if ($n >= 0 && $n < 2) return self::PLURAL_ONE;
	}
	
	// Indonesian
	private static function id($n) {}
	
	// Icelandic
	private static function is($n) {
		$f = self::getF($n);
		
		if ($f === 0 && $n % 10 === 1 && !($n % 100 === 11) || !($f === 0)) return self::PLURAL_ONE;
	}
	
	// Japanese
	private static function ja($n) {}
	
	// Javanese
	private static function jv($n) {}
	
	// Georgian
	private static function ka($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Kazakh
	private static function kk($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Khmer
	private static function km($n) {}
	
	// Kannada
	private static function kn($n) {
		if ($n >= 0 && $n <= 1) return self::PLURAL_ONE;
	}
	
	// Korean
	private static function ko($n) {}
	
	// Kurdish
	private static function ku($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Kyrgyz
	private static function ky($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Luxembourgish
	private static function lb($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Lao
	private static function lo($n) {}
	
	// Lithuanian
	private static function lt($n) {
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		
		if ($mod10 == 1 && !($mod100 >= 11 && $mod100 <= 19)) return self::PLURAL_ONE;
		if ($mod10 >= 2 && $mod10 <= 9 && !($mod100 >= 11 && $mod100 <= 19)) return self::PLURAL_FEW;
		if (self::getF($n) != 0) return self::PLURAL_MANY;
	}
	
	// Latvian
	private static function lv($n) {
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		$v = self::getV($n);
		$f = self::getF($n);
		$fMod10 = $f % 10;
		$fMod100 = $f % 100;
		
		if ($mod10 == 0 || ($mod100 >= 11 && $mod100 <= 19) || ($v == 2 && $fMod100 >= 11 && $fMod100 <= 19)) return self::PLURAL_ZERO;
		if (($mod10 == 1 && $mod100 != 11) || ($v == 2 && $fMod10 == 1 && $fMod100 != 11) || ($v != 2 && $fMod10 == 1)) return self::PLURAL_ONE;
	}
	
	// Macedonian
	private static function mk($n) {
		$v = self::getV($n);
		$f = self::getF($n);
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		$fMod10 = $f % 10;
		$fMod100 = $f % 100;
		
		if (($v == 0 && $mod10 == 1 && $mod100 != 11) || ($fMod10 == 1 && $fMod100 != 11)) return self::PLURAL_ONE;
	}
	
	// Malayalam
	private static function ml($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Mongolian 
	private static function mn($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Marathi 
	private static function mr($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Malay 
	private static function ms($n) {}
	
	// Maltese 
	private static function mt($n) {
		$mod100 = $n % 100;
		
		if ($n == 1) return self::PLURAL_ONE;
		if ($n == 0 || ($mod100 >= 2 && $mod100 <= 10)) return self::PLURAL_FEW;
		if ($mod100 >= 11 && $mod100 <= 19) return self::PLURAL_MANY;
	}
	
	// Burmese
	private static function my($n) {}
	
	// Norwegian
	private static function no($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Nepali
	private static function ne($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Odia
	private static function or($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Punjabi
	private static function pa($n) {
		if ($n == 1 || $n == 0) return self::PLURAL_ONE;
	}
	
	// Polish
	private static function pl($n) {
		$v = self::getV($n);
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		
		if ($n == 1 && $v == 0) return self::PLURAL_ONE;
		if ($v == 0 && $mod10 >= 2 && $mod10 <= 4 && !($mod100 >= 12 && $mod100 <= 14)) return self::PLURAL_FEW;
		if ($v == 0 && (($n != 1 && $mod10 >= 0 && $mod10 <= 1) || ($mod10 >= 5 && $mod10 <= 9) || ($mod100 >= 12 && $mod100 <= 14))) return self::PLURAL_MANY;
	}
	
	// Pashto
	private static function ps($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Portuguese
	private static function pt($n) {
		if ($n >= 0 && $n < 2) return self::PLURAL_ONE;
	}
	
	// Romanian
	private static function ro($n) {
		$v = self::getV($n);
		$mod100 = $n % 100;
		
		if ($n == 1 && $v === 0) return self::PLURAL_ONE;
		if ($v != 0 || $n == 0 || ($mod100 >= 2 && $mod100 <= 19)) return self::PLURAL_FEW;
	}
	
	// Russian
	private static function ru($n) {
		$mod10 = $n % 10;
		$mod100 = $n % 100;
		
		if (self::getV($n) == 0) {
			if ($mod10 == 1 && $mod100 != 11) return self::PLURAL_ONE;
			if ($mod10 >= 2 && $mod10 <= 4 && !($mod100 >= 12 && $mod100 <= 14)) return self::PLURAL_FEW;
			if ($mod10 == 0 || ($mod10 >= 5 && $mod10 <= 9) || ($mod100 >= 11 && $mod100 <= 14)) return self::PLURAL_MANY; 
		}
	}
	
	// Sindhi
	private static function sd($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Sinhala
	private static function si($n) {
		if ($n == 0 || $n == 1 || (floor($n) == 0 && self::getF($n) == 1)) return self::PLURAL_ONE;
	}
	
	// Slovak
	private static function sk($n) {
		// same as Czech
		return self::cs($n);
	}
	
	// Slovenian
	private static function sl($n) {
		$v = self::getV($n);
		$mod100 = $n % 100;
		
		if ($v == 0 && $mod100 == 1) return self::PLURAL_ONE;
		if ($v == 0 && $mod100 == 2) return self::PLURAL_TWO;
		if (($v == 0 && ($mod100 == 3 || $mod100 == 4)) || $v != 0) return self::PLURAL_FEW;
	}
	
	// Albanian
	private static function sq($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Serbian
	private static function sr($n) {
		// same as Bosnian
		return self::bs($n);
	}
	
	// Tamil
	private static function ta($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Telugu
	private static function te($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Tajik
	private static function tg($n) {}
	
	// Thai
	private static function th($n) {}
	
	// Turkmen
	private static function tk($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Turkish
	private static function tr($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Uyghur
	private static function ug($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Ukrainian
	private static function uk($n) {
		// same as Russian
		return self::ru($n);
	}
	
	// Uzbek
	private static function uz($n) {
		if ($n == 1) return self::PLURAL_ONE;
	}
	
	// Vietnamese
	private static function vi($n) {}
	
	// Chinese
	private static function zh($n) {}
}
