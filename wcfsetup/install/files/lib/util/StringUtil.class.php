<?php
namespace wcf\util;
use wcf\system\WCF;

/**
 * Contains string-related functions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
final class StringUtil {
	const HTML_PATTERN = '~</?[a-z]+[1-6]?
			(?:\s*[a-z]+\s*=\s*(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^\s>]
			))*\s*/?>~ix';
	const HTML_COMMENT_PATTERN = '~<!--(.*?)-->~';
	
	/**
	 * Returns a salted hash of the given value.
	 *
	 * @param 	string 		$value
	 * @param	string		$salt
	 * @return 	string 		$hash
	 */
	public static function getSaltedHash($value, $salt) {
		if (!defined('ENCRYPTION_ENABLE_SALTING') || ENCRYPTION_ENABLE_SALTING) {
			$hash = '';
			// salt
			if (!defined('ENCRYPTION_SALT_POSITION') || ENCRYPTION_SALT_POSITION == 'before') {
				$hash .= $salt;
			}
			
			// value
			if (!defined('ENCRYPTION_ENCRYPT_BEFORE_SALTING') || ENCRYPTION_ENCRYPT_BEFORE_SALTING) {
				$hash .= self::encrypt($value);
			}
			else {
				$hash .= $value;
			}
			
			// salt
			if (defined('ENCRYPTION_SALT_POSITION') && ENCRYPTION_SALT_POSITION == 'after') {
				$hash .= $salt;
			}
			
			return self::encrypt($hash);
		}
		else {
			return self::encrypt($value);
		}
	}
	
	/**
	 * Returns a double salted hash of the given value.
	 *
	 * @param 	string 		$value
	 * @param	string		$salt
	 * @return 	string 		$hash
	 */
	public static function getDoubleSaltedHash($value, $salt) {
		return self::encrypt($salt . self::getSaltedHash($value, $salt));
	}
	
	/**
	 * encrypts the given value.
	 *
	 * @param 	string 		$value
	 * @return 	string 		$hash
	 */
	public static function encrypt($value) {
		if (defined('ENCRYPTION_METHOD')) {
			switch (ENCRYPTION_METHOD) {
				case 'sha1': return sha1($value);
				case 'md5': return md5($value);
				case 'crc32': return crc32($value);
				case 'crypt': return crypt($value);
			}
		}
		return sha1($value);
	}
	
	/**
	 * alias to php sha1() function.
	 *
	 * @param 	string 		$value
	 * @return 	string 		$hash
	 */
	public static function getHash($value) {
		return sha1($value);
	}

	/**
	 * Creates a random hash.
	 * 
	 * @return	string		a random hash
	 */
	public static function getRandomID() {
		return self::getHash(microtime() . uniqid(mt_rand(), true));
	}

	/**
	 * Converts dos to unix newlines.
	 *
	 * @param 	string 		$string
	 * @return 	string 		$string
	 */
	public static function unifyNewlines($string) {
		return preg_replace("%(\r\n)|(\r)%", "\n", $string);
	}

	/**
	 * Swallowes whitespace from beginnung and end of the string.
	 * 
	 * @param 	string 		$string
	 * @return 	string 		$string
	 */
	public static function trim($text) {
		// Whitespace + (narrow) non breaking spaces.
		// No one can triforce now.
		$text = preg_replace('/^(\s|'.chr(226).chr(128).chr(175).'|'.chr(194).chr(160).')+/', '', $text);
		$text = preg_replace('/(\s|'.chr(226).chr(128).chr(175).'|'.chr(194).chr(160).')+$/', '', $text);
		return $text;
	}

	/**
	 * Converts html special characters.
	 *
	 * @param 	string 		$string
	 * @return 	string 		$string
	 */
	public static function encodeHTML($string) {
		if (is_object($string)) 
			$string = $string->__toString();
		
		return @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}
	
	/**
	 * Converts javascript special characters.
	 *
	 * @param 	string 		$string
	 * @return 	string 		$string
	 */
	public static function encodeJS($string) {
		if (is_object($string)) $string = $string->__toString();
		
		// escape backslash
		$string = StringUtil::replace("\\", "\\\\", $string);
		
		// escape singe quote
		$string = StringUtil::replace("'", "\'", $string);
		
		// escape new lines
		$string = StringUtil::replace("\n", '\n', $string);
		
		// escape slashes
		$string = StringUtil::replace("/", '\/', $string);
		
		return $string;
	}
	
	/**
	 * Decodes html entities.
	 *
	 * @param 	string 		$string
	 * @return 	string 		$string
	 */
	public static function decodeHTML($string) {
		$string = str_ireplace('&nbsp;', ' ', $string); // convert non-breaking spaces to ascii 32; not ascii 160
		return @html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Formats a numeric.
	 *
	 * @param 	numeric 	$numeric
	 * @return 	string 		
	 */
	public static function formatNumeric($numeric) {
		if (is_int($numeric)) 
			return self::formatInteger($numeric);
			
		else if (is_float($numeric))
			return self::formatDouble($numeric);
			
		else {
			if (floatval($numeric) - (float) intval($numeric))
				return self::formatDouble($numeric);
			else 
				return self::formatInteger(intval($numeric));
		}
	}
	
	/**
	 * Formats an integer.
	 * 
	 * @param	integer		$integer
	 * @return	string
	 */
	public static function formatInteger($integer) {
		$integer = self::addThousandsSeparator($integer);
		
		return $integer;
	}
	
	/**
	 * Formats a double.
	 * 
	 * @param	double		$double
	 * @param	integer		$maxDecimals
	 * @return	string
	 */
	public static function formatDouble($double, $maxDecimals = 0) {
		// consider as integer, if no decimal places found
		if (!$maxDecimals && preg_match('~^(-?\d+)(?:\.(?:0*|00[0-4]\d*))?$~', $double, $match)) {
			return self::formatInteger($match[1]);
		}
	
		// round
		$double = round($double, ($maxDecimals > 2 ? $maxDecimals : 2));
		
		// remove last 0
		if ($maxDecimals < 2 && substr($double, -1) == '0') $double = substr($double, 0, -1);
		
		// replace decimal point
		$double = str_replace('.', WCF::getLanguage()->get('wcf.global.decimalPoint'), $double);
		
		// add thousands separator
		$double = self::addThousandsSeparator($double);
		
		return $double;
	}
	
	/**
	 * Adds thousands separators to a given number.
	 * 
	 * @param	mixed		$number
	 * @return	string
	 */
	public static function addThousandsSeparator($number) {
		if ($number >= 1000 || $number <= -1000) {
			$number = preg_replace('~(?<=\d)(?=(\d{3})+(?!\d))~', WCF::getLanguage()->get('wcf.global.thousandsSeparator'), $number);
		}
		
		return $number;
	}
	
	/**
	 * Sorts an array of strings and maintain index association.
	 * 
	 * @param 	array		$strings 
	 * @return 	boolean
	 */
	public static function sort(array &$strings) {
		return asort($strings, SORT_LOCALE_STRING);
	}
		
	/**
	 * alias to php strlen() function.
	 */
	public static function length($string) {
		return mb_strlen($string);
	}
	
	/**
	 * alias to php strpos() function.
	 */
	public static function indexOf($hayStack, $needle, $offset = 0) {
		return mb_strpos($hayStack, $needle, $offset);
	}
	
	/**
	 * alias to php stripos() function.
	 */
	public static function indexOfIgnoreCase($hayStack, $needle, $offset = 0) {
		return mb_strpos(self::toLowerCase($hayStack), self::toLowerCase($needle), $offset);
	}
	
	/**
	 * alias to php strrpos() function.
	 */
	public static function lastIndexOf($hayStack, $needle) {
		return mb_strrpos($hayStack, $needle);
	}
	
	/**
	 * alias to php substr() function.
	 */
	public static function substring($string, $start, $length = null) {
		if ($length !== null) return mb_substr($string, $start, $length);
		return mb_substr($string, $start);
	}
	
	/**
	 * alias to php strtolower() function.
	 */
	public static function toLowerCase($string) {
		return mb_strtolower($string);
	}
	
	/**
	 * alias to php strtoupper() function.
	 */
	public static function toUpperCase($string) {
		return mb_strtoupper($string);
	}
	
	/**
	 * alias to php substr_count() function.
	 */
	public static function countSubstring($hayStack, $needle) {
		return mb_substr_count($hayStack, $needle);
	}
	
	/**
	 * alias to php ucfirst() function.
	 */
	public static function firstCharToUpperCase($string) {
		return self::toUpperCase(self::substring($string, 0, 1)).self::substring($string, 1);
	}
	
	/**
	 * alias to php lcfirst() function.
	 */
	public static function firstCharToLowerCase($string) {
		return self::toLowerCase(self::substring($string, 0, 1)).self::substring($string, 1);
	}
	
	/**
	 * alias to php ucwords() function.
	 */
	public static function wordsToUpperCase($string) {
		return mb_convert_case($string, MB_CASE_TITLE);
	}
	
	/**
	 * alias to php str_replace() function.
	 */
	public static function replace($search, $replace, $subject, &$count = null) {
		return str_replace($search, $replace, $subject, $count);
	}
	
	/**
	 * alias to php str_ireplace() function.
	 */
	public static function replaceIgnoreCase($search, $replace, $subject, &$count = 0) {
		$startPos = self::indexOf(self::toLowerCase($subject), self::toLowerCase($search));
		if ($startPos === false) return $subject;
		else {
			$endPos = $startPos + self::length($search);
			$count++;
			return self::substring($subject, 0, $startPos) . $replace . self::replaceIgnoreCase($search, $replace, self::substring($subject, $endPos), $count);
		}
	}
	
	/**
	 * Unescapes escaped characters in a string.
	 * 
	 * @param	string		$string
	 * @param	string		$chars
	 * @return 	string
	 */
	public static function unescape($string, $chars = '"') {
		for ($i = 0, $j = strlen($chars); $i < $j; $i++) {
			$string = self::replace('\\'.$chars[$i], $chars[$i], $string);
		}
		
		return $string;
	}
	
	/**
	 * Takes a numeric HTML entity value and returns the appropriate UTF-8 bytes.
	 * 
	 * @param	integer		$dec		html entity value
	 * @return	string				utf-8 bytes
	 */
	public static function getCharacter($dec) {
		if ($dec < 128) {
			$utf = chr($dec);
		}
		else if ($dec < 2048) {
			$utf = chr(192 + (($dec - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		}
		else {
			$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
			$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		}
		return $utf;
	}
	
	/**
	 * Converts UTF-8 to Unicode
	 * @see		http://www1.tip.nl/~t876506/utf8tbl.html
	 *
	 * @param	string		$c
	 * @return	integer		unicode value of $c
	 */
	public static function getCharValue($c) {
		$ud = 0;
		if (ord($c{0}) >= 0 && ord($c{0}) <= 127) 
			$ud = ord($c{0});
		if (ord($c{0}) >= 192 && ord($c{0}) <= 223) 
			$ud = (ord($c{0}) - 192) * 64 + (ord($c{1}) - 128);
		if (ord($c{0}) >= 224 && ord($c{0}) <= 239) 
			$ud = (ord($c{0}) - 224) * 4096 + (ord($c{1}) - 128) * 64 + (ord($c{2}) - 128);
		if (ord($c{0}) >= 240 && ord($c{0}) <= 247) 
			$ud = (ord($c{0}) - 240) * 262144 + (ord($c{1}) - 128) * 4096 + (ord($c{2}) - 128) * 64 + (ord($c{3}) - 128);
		if (ord($c{0}) >= 248 && ord($c{0}) <= 251) 
			$ud = (ord($c{0}) - 248) * 16777216 + (ord($c{1}) - 128) * 262144 + (ord($c{2}) - 128) * 4096 + (ord($c{3}) - 128) * 64 + (ord($c{4}) - 128);
		if (ord($c{0}) >= 252 && ord($c{0}) <= 253) 
			$ud = (ord($c{0}) - 252) * 1073741824 + (ord($c{1}) - 128) * 16777216 + (ord($c{2}) - 128) * 262144 + (ord($c{3}) - 128) * 4096 + (ord($c{4}) - 128) * 64 + (ord($c{5}) - 128);
		if (ord($c{0}) >= 254 && ord($c{0}) <= 255) 
			$ud = false; // error
		return $ud;
	}
	
	/**
	 * Returns html entities of all characters in the given string.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeAllChars($string) {
		$result = '';
		for ($i = 0, $j = StringUtil::length($string); $i < $j; $i++) {
			$char = StringUtil::substring($string, $i, 1);
			$result .= '&#'.StringUtil::getCharValue($char).';';
		}
		
		return $result;
	}
	
	/**
	 * Returns true, if the given string contains only ASCII characters.
	 * 
	 * @param	string		$string
	 * @return	boolean
	 */
	public static function isASCII($string) {
		return preg_match('/^[\x00-\x7F]*$/', $string);
	}
	
	/**
	 * Returns true, if the given string is utf-8 encoded.
	 * @see		http://www.w3.org/International/questions/qa-forms-utf-8
	 * 
	 * @param	string		$string
	 * @return	boolean
	 */
	public static function isUTF8($string) {
		/*return preg_match('/^(
				[\x09\x0A\x0D\x20-\x7E]*		# ASCII
			|	[\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
			|	\xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
			|	[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|	\xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
			|	\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
			|	[\xF1-\xF3][\x80-\xBF]{3}		# planes 4-15
			|	\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
			)*$/x', $string);
		*/	
		return preg_match('/(
				[\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
			|	\xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
			|	[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|	\xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
			|	\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
			|	[\xF1-\xF3][\x80-\xBF]{3}		# planes 4-15
			|	\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
			)/x', $string);
	}
	
	/**
	 * Escapes the closing cdata tag.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function escapeCDATA($string) {
		return str_replace(']]>', ']]]]><![CDATA[>', $string);
	}
	
	/**
	 * Converts a string to requested character encoding.
	 * @see		mb_convert_encoding()
	 * 
	 * @param 	string		$inCharset
	 * @param 	string		$outCharset
	 * @param 	string		$string
	 * @return 	string		converted string
	 */
	public static function convertEncoding($inCharset, $outCharset, $string) {
		if ($inCharset == 'ISO-8859-1' && $outCharset == 'UTF-8') return utf8_encode($string);
		if ($inCharset == 'UTF-8' && $outCharset == 'ISO-8859-1') return utf8_decode($string);
		
		return mb_convert_encoding($string, $outCharset, $inCharset);
	}
	
	/**
	 * Strips HTML tags from a string.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function stripHTML($string) {
		return preg_replace(self::HTML_PATTERN, '', preg_replace(self::HTML_COMMENT_PATTERN, '', $string));
	}
	
	/**
	 * Returns false, if the given word is forbidden by given word filter.
	 * 
	 * @param 	string		$word
	 * @param	string		$filter
	 * @return	boolean
	 */
	public static function executeWordFilter($word, $filter) {
		$word = self::toLowerCase($word);
		
		if ($filter != '') {
			$forbiddenNames = explode("\n", self::toLowerCase(self::unifyNewlines($filter)));
			foreach ($forbiddenNames as $forbiddenName) {
				if (self::indexOf($forbiddenName, '*') !== false) {
					$forbiddenName = self::replace('\*', '.*', preg_quote($forbiddenName, '/'));
					if (preg_match('/^'.$forbiddenName.'$/s', $word)) {
						return false;
					}
				}
				else {
					if ($word == $forbiddenName) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Splits given string into smaller chunks.
	 * 
	 * @param	string		$string
	 * @param	integer		$length
	 * @param	string		$break
	 * @return	string
	 */
	public static function splitIntoChunks($string, $length = 75, $break = "\r\n") {
		return mb_ereg_replace('.{'.$length.'}', "\\0".$break, $string);
	}
	
	/**
	 * Generates a random user password with the given character length.
	 *
	 * @param	integer		$length
	 * @return	string		new password
	 */
	public static function getRandomPassword($length = 8) {
		$availableCharacters = array(
			0 => 'abcdefghijklmnopqrstuvwxyz',
			1 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			2 => '0123456789',
			3 => '+#-.,;:?!'
		);
		
		$password = '';
		$type = 0;
		for ($i = 0; $i < $length; $i++) {
			$type = ($i % 4 == 0) ? 0 : ($type + 1);
			$password .= substr($availableCharacters[$type], MathUtil::getRandomValue(0, strlen($availableCharacters[$type]) - 1), 1);
		}
		
		return str_shuffle($password);
	}
	
	private function __construct() { }
}
