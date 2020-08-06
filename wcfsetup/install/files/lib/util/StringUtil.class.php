<?php
namespace wcf\util;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * Contains string-related functions.
 * 
 * @author	Oliver Kliebisch, Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class StringUtil {
	const HTML_PATTERN = '~</?[a-z]+[1-6]?
			(?:\s*[a-z\-]+\s*(=\s*(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^\s>]
			))?)*\s*/?>~ix';
	const HTML_COMMENT_PATTERN = '~<!--(.*?)-->~';
	
	/**
	 * utf8 bytes of the HORIZONTAL ELLIPSIS (U+2026)
	 * @var	string
	 */
	const HELLIP = "\u{2026}";
	
	/**
	 * utf8 bytes of the MINUS SIGN (U+2212)
	 * @var	string
	 */
	const MINUS = "\u{2212}";
	
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
	 * Returns a 40 character hexadecimal string generated using a CSPRNG.
	 * 
	 * @return	string
	 */
	public static function getRandomID() {
		return \bin2hex(\random_bytes(20));
	}
	
	/**
	 * Creates an UUID.
	 * 
	 * @return	string
	 */
	public static function getUUID() {
		return sprintf(
			'%04x%04x-%04x-%04x-%02x%02x-%04x%04x%04x',
			// time_low
			\random_int(0, 0xffff), \random_int(0, 0xffff),
			// time_mid
			\random_int(0, 0xffff), 
			// time_hi_and_version
			\random_int(0, 0x0fff) | 0x4000,
			// clock_seq_hi_and_res
			\random_int(0, 0x3f) | 0x80,
			// clock_seq_low
			\random_int(0, 0xff),
			// node
			\random_int(0, 0xffff), \random_int(0, 0xffff), \random_int(0, 0xffff)
		);
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
	 * Removes Unicode whitespace characters from the beginning
	 * and ending of the given string.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	public static function trim($text) {
		// These regular expressions use character properties
		// to find characters defined as space in the unicode
		// specification.
		// Do not merge the expressions, they are separated for
		// performance reasons.
		$text = preg_replace('/^[\p{Zs}\s]+/u', '', $text);
		$text = preg_replace('/[\p{Zs}\s]+$/u', '', $text);
		
		return $text;
	}
	
	/**
	 * Converts html special characters.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeHTML($string) {
		return @htmlspecialchars((string) $string, ENT_COMPAT, 'UTF-8');
	}
	
	/**
	 * Converts javascript special characters.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeJS($string) {
		$string = self::unifyNewlines($string);
		$string = str_replace(["\\", "'", "\n", "/"], ["\\\\", "\'", '\n', '\/'], $string);
		
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
		$string = str_replace("\'", "&#39;", $string);
		
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
	 * @param	number		$numeric
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
		// round
		$double = (string) round($double, ($maxDecimals > 0 ? $maxDecimals : 2));
		
		// consider as integer, if no decimal places found
		if (!$maxDecimals && preg_match('~^(-?\d+)(?:\.(?:0*|00[0-4]\d*))?$~', $double, $match)) {
			return self::formatInteger($match[1]);
		}
		
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
	 * Replaces the MINUS-HYPHEN with the MINUS SIGN.
	 * 
	 * @param	mixed		$number
	 * @return	string
	 */
	public static function formatNegative($number) {
		return str_replace('-', self::MINUS, $number);
	}
	
	/**
	 * Alias to php ucfirst() function with multibyte support.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function firstCharToUpperCase($string) {
		return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
	}
	
	/**
	 * Alias to php lcfirst() function with multibyte support.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function firstCharToLowerCase($string) {
		return mb_strtolower(mb_substr($string, 0, 1)).mb_substr($string, 1);
	}
	
	/**
	 * Alias to php mb_convert_case() function.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function wordsToUpperCase($string) {
		return mb_convert_case($string, MB_CASE_TITLE);
	}
	
	/**
	 * Alias to php str_ireplace() function with UTF-8 support.
	 * 
	 * This function is considered to be slow, if $search contains
	 * only ASCII characters, please use str_ireplace() instead.
	 * 
	 * @param	string		$search
	 * @param	string		$replace
	 * @param	string		$subject
	 * @param	integer		$count
	 * @return	string
	 */
	public static function replaceIgnoreCase($search, $replace, $subject, &$count = 0) {
		$startPos = mb_strpos(mb_strtolower($subject), mb_strtolower($search));
		if ($startPos === false) return $subject;
		else {
			$endPos = $startPos + mb_strlen($search);
			$count++;
			return mb_substr($subject, 0, $startPos) . $replace . self::replaceIgnoreCase($search, $replace, mb_substr($subject, $endPos), $count);
		}
	}
	
	/**
	 * Alias to php str_split() function with multibyte support.
	 * 
	 * @param	string		$string
	 * @param	integer		$length
	 * @return	string[]
	 */
	public static function split($string, $length = 1) {
		$result = [];
		for ($i = 0, $max = mb_strlen($string); $i < $max; $i += $length) {
			$result[] = mb_substr($string, $i, $length);
		}
		return $result;
	}
	
	/**
	 * Checks whether $haystack starts with $needle, or not.
	 * 
	 * @param	string		$haystack	The string to be checked for starting with $needle
	 * @param	string		$needle		The string to be found at the start of $haystack
	 * @param	boolean		$ci		Case insensitive or not. Default = false.
	 * 
	 * @return	boolean				True, if $haystack starts with $needle, false otherwise.
	 */
	public static function startsWith($haystack, $needle, $ci = false) {
		if ($ci) {
			$haystack = mb_strtolower($haystack);
			$needle = mb_strtolower($needle);
		}
		// using mb_substr and === is MUCH faster for long strings then using indexOf.
		return mb_substr($haystack, 0, mb_strlen($needle)) === $needle;
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
			$haystack = mb_strtolower($haystack);
			$needle = mb_strtolower($needle);
		}
		$length = mb_strlen($needle);
		if ($length === 0) return true;
		return (mb_substr($haystack, $length * -1) === $needle);
	}
	
	/**
	 * Alias to php str_pad function with multibyte support.
	 * 
	 * @param	string		$input
	 * @param	integer		$padLength
	 * @param	string		$padString
	 * @param	integer		$padType
	 * @return	string
	 */
	public static function pad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT) {
		$additionalPadding = strlen($input) - mb_strlen($input);
		return str_pad($input, $padLength + $additionalPadding, $padString, $padType);
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
			$string = str_replace('\\'.$chars[$i], $chars[$i], $string);
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
		if (ord($c[0]) >= 0 && ord($c[0]) <= 127) 
			$ud = ord($c[0]);
		if (ord($c[0]) >= 192 && ord($c[0]) <= 223) 
			$ud = (ord($c[0]) - 192) * 64 + (ord($c[1]) - 128);
		if (ord($c[0]) >= 224 && ord($c[0]) <= 239) 
			$ud = (ord($c[0]) - 224) * 4096 + (ord($c[1]) - 128) * 64 + (ord($c[2]) - 128);
		if (ord($c[0]) >= 240 && ord($c[0]) <= 247) 
			$ud = (ord($c[0]) - 240) * 262144 + (ord($c[1]) - 128) * 4096 + (ord($c[2]) - 128) * 64 + (ord($c[3]) - 128);
		if (ord($c[0]) >= 248 && ord($c[0]) <= 251) 
			$ud = (ord($c[0]) - 248) * 16777216 + (ord($c[1]) - 128) * 262144 + (ord($c[2]) - 128) * 4096 + (ord($c[3]) - 128) * 64 + (ord($c[4]) - 128);
		if (ord($c[0]) >= 252 && ord($c[0]) <= 253) 
			$ud = (ord($c[0]) - 252) * 1073741824 + (ord($c[1]) - 128) * 16777216 + (ord($c[2]) - 128) * 262144 + (ord($c[3]) - 128) * 4096 + (ord($c[4]) - 128) * 64 + (ord($c[5]) - 128);
		if (ord($c[0]) >= 254 && ord($c[0]) <= 255) 
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
		for ($i = 0, $j = mb_strlen($string); $i < $j; $i++) {
			$char = mb_substr($string, $i, 1);
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
		return preg_match('/^(
				[\x09\x0A\x0D\x20-\x7E]*		# ASCII
			|	[\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
			|	\xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
			|	[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|	\xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
			|	\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
			|	[\xF1-\xF3][\x80-\xBF]{3}		# planes 4-15
			|	\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
			)*$/x', $string);
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
		$filter = self::trim($filter);
		$word = mb_strtolower($word);
		
		if ($filter != '') {
			$forbiddenNames = explode("\n", mb_strtolower(self::unifyNewlines($filter)));
			foreach ($forbiddenNames as $forbiddenName) {
				// ignore empty lines in between actual values
				$forbiddenName = self::trim($forbiddenName);
				if (empty($forbiddenName)) continue;
				
				if (mb_strpos($forbiddenName, '*') !== false) {
					$forbiddenName = str_replace('\*', '.*', preg_quote($forbiddenName, '/'));
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
		
		if (mb_strlen($string) > $length) {
			$length -= mb_strlen($etc);
			
			if (!$breakWords) {
				$string = preg_replace('/\\s+?(\\S+)?$/', '', mb_substr($string, 0, $length + 1));
			}
			
			return mb_substr($string, 0, $length).$etc;
		}
		else {
			return $string;
		}
	}
	
	/**
	 * Truncates a string containing HTML code and keeps the HTML syntax intact.
	 * 
	 * @param	string		$string			string which shall be truncated
	 * @param	integer		$length			string length after truncating
	 * @param	string		$etc			ending string which will be appended after truncating
	 * @param	boolean		$breakWords		if false words will not be split and the return string might be shorter than $length
	 * @return	string					truncated string
	 */
	public static function truncateHTML($string, $length = 500, $etc = self::HELLIP, $breakWords = false) {
		if (mb_strlen(self::stripHTML($string)) <= $length) {
			return $string;
		}
		$openTags = [];
		$truncatedString = '';
		
		// initialize length counter with the ending length
		$totalLength = mb_strlen($etc);
		
		preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $string, $tags, PREG_SET_ORDER);
		
		foreach ($tags as $tag) {
			// ignore void elements
			if (!preg_match('/^(area|base|br|col|embed|hr|img|input|keygen|link|menuitem|meta|param|source|track|wbr)$/s', $tag[2])) {
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
			$contentLength = mb_strlen($decodedContent);
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
							$entitiesLength += mb_strlen($entity[0]);
						}
						else {
							break;
						}
					}
				}
				$truncatedString .= mb_substr($tag[3], 0, $left + $entitiesLength);
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
	 * @param       boolean         $isUgc          true to add rel=ugc to the anchor tag 
	 * @return	string		anchor tag
	 */
	public static function getAnchorTag($url, $title = '', $encodeTitle = true, $isUgc = false) {
		$url = self::trim($url);
		
		// cut visible url
		if (empty($title)) {
			// use URL and remove protocol and www subdomain
			$title = preg_replace('~^(?:https?|ftps?)://(?:www\.)?~i', '', $url);
			
			if (mb_strlen($title) > 60) {
				$title = mb_substr($title, 0, 30) . self::HELLIP . mb_substr($title, -25);
			}
			
			if (!$encodeTitle) $title = self::encodeHTML($title);
		}
		
		return '<a '. self::getAnchorTagAttributes($url, $isUgc) .'>' . ($encodeTitle ? self::encodeHTML($title) : $title) . '</a>';
	}
	
	/**
	 * Generates the attributes for an anchor tag from given URL.
	 *
	 * @param	string		$url
	 * @param       boolean         $isUgc          true to add rel=ugc to the attributes
	 * @return	string		attributes
	 * @since       5.3
	 */
	public static function getAnchorTagAttributes($url, $isUgc = false) {
		$external = true;
		if (ApplicationHandler::getInstance()->isInternalURL($url)) {
			$external = false;
			$url = preg_replace('~^https?://~', RouteHandler::getProtocol(), $url);
		}
		
		$attributes = 'href="' . self::encodeHTML($url) . '"';
		if ($external) {
			$attributes .= ' class="externalURL"';
			$rel = 'nofollow';
			if (EXTERNAL_LINK_TARGET_BLANK) {
				$rel .= ' noopener noreferrer';
				$attributes .= 'target="_blank"';
			}
			if ($isUgc) {
				$rel .= ' ugc';
			}
			
			$attributes .= ' rel="' . $rel . '"';
		}
		
		return $attributes;
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
	 * @param	string		$string
	 * @param	integer		$width
	 * @param	string		$break
	 * @return	string
	 */
	public static function wordwrap($string, $width = 50, $break = ' ') {
		$result = '';
		$substrings = explode($break, $string);
		
		foreach ($substrings as $substring) {
			$length = mb_strlen($substring);
			if ($length > $width) {
				$j = ceil($length / $width);
				
				for ($i = 0; $i < $j; $i++) {
					if (!empty($result)) $result .= $break;
					if ($width * ($i + 1) > $length) $result .= mb_substr($substring, $width * $i);
					else $result .= mb_substr($substring, $width * $i, $width);
				}
			}
			else {
				if (!empty($result)) $result .= $break;
				$result .= $substring;
			}
		}
		
		return $result;
	}
	
	/**
	 * Shortens numbers larger than 1000 by using unit suffixes.
	 * 
	 * @param       integer         $number
	 * @return      string
	 */
	public static function getShortUnit($number) {
		$unitSuffix = '';
		
		if ($number >= 1000000) {
			$number /= 1000000;
			if ($number > 10) {
				$number = floor($number);
			}
			else {
				$number = round($number, 1);
			}
			$unitSuffix = 'M';
		}
		else if ($number >= 1000) {
			$number /= 1000;
			if ($number > 10) {
				$number = floor($number);
			}
			else {
				$number = round($number, 1);
			}
			$unitSuffix = 'k';
		}
		
		return self::formatNumeric($number) . $unitSuffix;
	}
	
	/**
	 * Normalizes a string representing comma-separated values by making sure
	 * that the separator is just a comma, not a combination of whitespace and
	 * a comma.
	 * 
	 * @param	string		$string
	 * @return	string
	 * @since	3.1
	 */
	public static function normalizeCsv($string) {
		return implode(',', ArrayUtil::trim(explode(',', $string)));
	}
	
	/**
	 * Forbid creation of StringUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
