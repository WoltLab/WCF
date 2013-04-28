<?php
namespace wcf\util;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

/**
 * Contains string-related functions.
 * 
 * @author	Oliver Kliebisch, Marcel Werk 
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class StringUtil {
	const HTML_PATTERN = '~</?[a-z]+[1-6]?
			(?:\s*[a-z]+\s*=\s*(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^\s>]
			))*\s*/?>~ix';
	const HTML_COMMENT_PATTERN = '~<!--(.*?)-->~';
	
	/**
	 * utf8 bytes of the HORIZONTAL ELLIPSIS (U+2026)
	 * @var	string
	 */
	const HELLIP = "\xE2\x80\xA6";
	
	/**
	 * utf8 bytes of the MINUS SIGN (U+2212)
	 * @var	string
	 */
	const MINUS = "\xE2\x88\x92";
	
	/**
	 * Alias to php sha1() function.
	 * 
	 * @param	string		$value
	 * @return	string
	 */
	public static function getHash($value) {
		return sha1($value);
	}
	
	/**
	 * Creates a random hash.
	 * 
	 * @return	string
	 */
	public static function getRandomID() {
		return self::getHash(microtime() . uniqid(mt_rand(), true));
	}
	
	/**
	 * Converts dos to unix newlines.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function unifyNewlines($string) {
		return preg_replace("%(\r\n)|(\r)%", "\n", $string);
	}
	
	/**
	 * Swallowes whitespace from beginnung and end of the string.
	 * 
	 * @param	string		$string
	 * @return	string
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
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeHTML($string) {
		if (is_object($string)) 
			$string = $string->__toString();
		
		return @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}
	
	/**
	 * Converts javascript special characters.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeJS($string) {
		if (is_object($string)) $string = $string->__toString();
		
		// escape backslash
		$string = self::replace("\\", "\\\\", $string);
		
		// escape singe quote
		$string = self::replace("'", "\'", $string);
		
		// escape new lines
		$string = self::replace("\n", '\n', $string);
		
		// escape slashes
		$string = self::replace("/", '\/', $string);
		
		return $string;
	}
	
	/**
	 * Encodes JSON strings. This is not the same as PHP's json_encode()!
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeJSON($string) {
		$string = self::encodeJS($string);
		
		$string = self::encodeHTML($string);
		
		// single quotes must be encoded as HTML entity
		$string = self::replace("\'", "&#39;", $string);
		
		return $string;
	}
	
	/**
	 * Decodes html entities.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function decodeHTML($string) {
		$string = str_ireplace('&nbsp;', ' ', $string); // convert non-breaking spaces to ascii 32; not ascii 160
		return @html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}
	
	/**
	 * Formats a numeric.
	 * 
	 * @param	numeric		$numeric
	 * @return	string
	 */
	public static function formatNumeric($numeric) {
		if (is_int($numeric)) {
			return self::formatInteger($numeric);
		}
		else if (is_float($numeric)) {
			return self::formatDouble($numeric);
		}
		else {
			if (floatval($numeric) - (float) intval($numeric)) {
				return self::formatDouble($numeric);
			}
			else {
				return self::formatInteger(intval($numeric));
			}
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
		
		// format minus
		$integer = self::formatNegative($integer);
		
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
		
		// format minus
		$double = self::formatNegative($double);
		
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
	 * Replaces the MINUS-HYPHEN with the MINUS SIGN
	 * 
	 * @param	mixed		$number
	 * @return	string
	 */
	public static function formatNegative($number) {
		return self::replace('-', self::MINUS, $number);
	}
	
	/**
	 * Sorts an array of strings and maintain index association.
	 * 
	 * @param	array		$strings 
	 * @return	boolean
	 */
	public static function sort(array &$strings) {
		return asort($strings, SORT_LOCALE_STRING);
	}
	
	/**
	 * Alias to php mb_strlen() function.
	 */
	public static function length($string) {
		return mb_strlen($string);
	}
	
	/**
	 * Alias to php mb_strpos() function.
	 */
	public static function indexOf($hayStack, $needle, $offset = 0) {
		return mb_strpos($hayStack, $needle, $offset);
	}
	
	/**
	 * Alias to php stripos() function with multibyte support.
	 */
	public static function indexOfIgnoreCase($hayStack, $needle, $offset = 0) {
		return mb_strpos(self::toLowerCase($hayStack), self::toLowerCase($needle), $offset);
	}
	
	/**
	 * Alias to php mb_strrpos() function.
	 */
	public static function lastIndexOf($hayStack, $needle) {
		return mb_strrpos($hayStack, $needle);
	}
	
	/**
	 * Alias to php mb_substr() function.
	 */
	public static function substring($string, $start, $length = null) {
		if ($length !== null) return mb_substr($string, $start, $length);
		return mb_substr($string, $start);
	}
	
	/**
	 * Alias to php mb_strtolower() function.
	 */
	public static function toLowerCase($string) {
		return mb_strtolower($string);
	}
	
	/**
	 * Alias to php mb_strtoupper() function.
	 */
	public static function toUpperCase($string) {
		return mb_strtoupper($string);
	}
	
	/**
	 * Alias to php substr_count() function.
	 */
	public static function countSubstring($hayStack, $needle) {
		return mb_substr_count($hayStack, $needle);
	}
	
	/**
	 * Alias to php ucfirst() function with multibyte support.
	 */
	public static function firstCharToUpperCase($string) {
		return self::toUpperCase(self::substring($string, 0, 1)).self::substring($string, 1);
	}
	
	/**
	 * Alias to php lcfirst() function with multibyte support.
	 */
	public static function firstCharToLowerCase($string) {
		return self::toLowerCase(self::substring($string, 0, 1)).self::substring($string, 1);
	}
	
	/**
	 * Alias to php mb_convert_case() function.
	 */
	public static function wordsToUpperCase($string) {
		return mb_convert_case($string, MB_CASE_TITLE);
	}
	
	/**
	 * Alias to php str_replace() function.
	 */
	public static function replace($search, $replace, $subject, &$count = null) {
		return str_replace($search, $replace, $subject, $count);
	}
	
	/**
	 * Alias to php str_ireplace() function with multibyte support.
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
	 * Checks wether $haystack starts with $needle, or not.
	 * 
	 * @param	string		$haystack	The string to be checked for starting with $needle
	 * @param	string		$needle		The string to be found at the start of $haystack
	 * @param	boolean		$ci		Case insensitive or not. Default = false.
	 * 
	 * @return	boolean				True, if $haystack starts with $needle, false otherwise.
	 */
	public static function startsWith($haystack, $needle, $ci = false) {
		if ($ci) {
			$haystack = self::toLowerCase($haystack);
			$needle = self::toLowerCase($needle);
		}
		// using substring and === is MUCH faster for long strings then using indexOf.
		return self::substring($haystack, 0, self::length($needle)) === $needle;
	}
	
	/**
	 * Returns true if $haystack ends with $needle or if the length of $needle is 0.
	 * 
	 * @param	string		$haystack
	 * @param	string		$needle	
	 * @param	boolean		$ci		case insensitive
	 * @return	boolean
	 */
	public static function endsWith($haystack, $needle, $ci = false) {
		if ($ci) {
			$haystack = self::toLowerCase($haystack);
			$needle = self::toLowerCase($needle);
		}
		$length = self::length($needle);
		if ($length === 0) return true;
		return (self::substring($haystack, $length * -1) === $needle);
	}
	
	/**
	 * Unescapes escaped characters in a string.
	 * 
	 * @param	string		$string
	 * @param	string		$chars
	 * @return	string
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
	 * @return	integer
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
		for ($i = 0, $j = self::length($string); $i < $j; $i++) {
			$char = self::substring($string, $i, 1);
			$result .= '&#'.self::getCharValue($char).';';
		}
		
		return $result;
	}
	
	/**
	 * Returns true if the given string contains only ASCII characters.
	 * 
	 * @param	string		$string
	 * @return	boolean
	 */
	public static function isASCII($string) {
		return preg_match('/^[\x00-\x7F]*$/', $string);
	}
	
	/**
	 * Returns true if the given string is utf-8 encoded.
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
	 * @param	string		$inCharset
	 * @param	string		$outCharset
	 * @param	string		$string
	 * @return	string		converted string
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
	 * Returns false if the given word is forbidden by given word filter.
	 * 
	 * @param	string		$word
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
	 * Truncates the given string to a certain number of characters.
	 * 
	 * @param	string		$string		string which shall be truncated
	 * @param	integer		$length		string length after truncating
	 * @param	string		$etc		string to append when $string is truncated
	 * @param	boolean		$breakWords	should words be broken in the middle
	 * @return	string				truncated string
	 */
	public static function truncate($string, $length = 80, $etc = self::HELLIP, $breakWords = false) {
		if ($length == 0) {
			return '';
		}
		
		if (self::length($string) > $length) {
			$length -= self::length($etc);
			
			if (!$breakWords) {
				$string = preg_replace('/\\s+?(\\S+)?$/', '', self::substring($string, 0, $length + 1));
			}
			
			return self::substring($string, 0, $length).$etc;
		}
		else {
			return $string;
		}
	}
	
	/**
	 * Truncates a string containing HTML code and keeps the HTML syntax intact.
	 *
	 * @param 	string		$string			string which shall be truncated
	 * @param 	integer		$length 		string length after truncating
	 * @param 	string		$etc			ending string which will be appended after truncating
	 * @param	boolean		$breakWords		if false words will not be split and the return string might be shorter than $length
	 * @return 	string					truncated string
	 */
	public static function truncateHTML($string, $length = 500, $etc = self::HELLIP, $breakWords = false) {
		if (self::length(self::stripHTML($string)) <= $length) {
			return $string;
		}
		$openTags = array();
		$truncatedString = '';
	
		// initalize length counter with the ending length
		$totalLength = self::length($etc);
	
		preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $string, $tags, PREG_SET_ORDER);
	
		foreach ($tags as $tag) {
			// filter standalone html tags
			if (!preg_match('/area|base|basefont|br|col|frame|hr|img|input|isindex|link|meta|param/s', $tag[2])) {
				// look for opening tags
				if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
					array_unshift($openTags, $tag[2]);
				}
				/**
				 * look for closing tags and check if this tag has a corresponding opening tag
				 * and omit the opening tag if it has been closed already
				 */
				else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
					$position = array_search($closeTag[1], $openTags);
					if ($position !== false) {
						array_splice($openTags, $position, 1);
					}
				}
			}
			// append tag
			$truncatedString .= $tag[1];
	
			// get length of the content without entities. If the content is too long, keep entities intact
			$decodedContent = self::decodeHTML($tag[3]);
			$contentLength = self::length($decodedContent);
			if ($contentLength + $totalLength > $length) {
				if (!$breakWords) {
					if (preg_match('/^(.{1,'.($length - $totalLength).'}) /s', $decodedContent, $match)) {
						$truncatedString .= self::encodeHTML($match[1]);
					}
					
					break;
				}
				
				$left = $length - $totalLength;
				$entitiesLength = 0;
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
					foreach ($entities[0] as $entity) {
						if ($entity[1] + 1 - $entitiesLength <= $left) {
							$left--;
							$entitiesLength += self::length($entity[0]);
						}
						else {
							break;
						}
					}
				}
				$truncatedString .= self::substring($tag[3], 0, $left + $entitiesLength);
				break;
			}
			else {
				$truncatedString .= $tag[3];
				$totalLength += $contentLength;
			}
			if ($totalLength >= $length) {
				break;
			}
		}
	
		// close all open tags
		foreach ($openTags as $tag) {
			$truncatedString .= '</'.$tag.'>';
		}
	
		// add etc
		$truncatedString .= $etc;
		
		return $truncatedString;
	}
	
	/**
	 * Generates an anchor tag from given URL.
	 *  
	 * @param	string		$url
	 * @param	string		$title
	 * @param	boolean		$encodeTitle
	 * @return	string		anchor tag
	 */
	public static function getAnchorTag($url, $title = '', $encodeTitle = true) {
		$external = true;
		if (ApplicationHandler::getInstance()->isInternalURL($url)) {
			$external = false;
		}
		
		// cut visible url
		if (empty($title)) {
			// use URL and remove protocol and www subdomain 
			$title = preg_replace('~^(?:https?|ftps?)://(?:www\.)?~i', '', $url);
			
			if (self::length($title) > 60) {
				$title = self::substring($title, 0, 30) . self::HELLIP . self::substring($title, -25);
			}
			
			if (!$encodeTitle) $title = self::encodeHTML($title);
		}
		
		return '<a href="'.self::encodeHTML($url).'"'.($external ? (' class="externalURL"'.(EXTERNAL_LINK_REL_NOFOLLOW ? ' rel="nofollow"' : '').(EXTERNAL_LINK_TARGET_BLANK ? ' target="_blank"' : '')) : '').'>'.($encodeTitle ? self::encodeHTML($title) : $title).'</a>';
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
	 * Simple multi-byte safe wordwrap() function.
	 * 
	 * @param 	string		$string
	 * @param	integer		$width
	 * @param	string		$break
	 * @return	string
	 */
	public static function wordwrap($string, $width = 50, $break = ' ') {
		$result = '';
		$substrings = explode($break, $string);
		
		foreach ($substrings as $substring) {
			$length = self::length($substring);
			if ($length > $width) {
				$j = ceil($length / $width);
				
				for ($i = 0; $i < $j; $i++) {
					if (!empty($result)) $result .= $break;
					if ($width * ($i + 1) > $length) $result .= self::substring($substring, $width * $i);
					else $result .= self::substring($substring, $width * $i, $width);
				}
			}
			else {
				if (!empty($result)) $result .= $break;
				$result .= $substring;
			}
		}
		
		return $result;
	}
	
	private function __construct() { }
}
