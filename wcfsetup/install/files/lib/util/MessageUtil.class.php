<?php
namespace wcf\util;
use wcf\system\Callback;
use wcf\system\Regex;

/**
 * Contains message-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
class MessageUtil {
	/**
	 * Strips session links, html entities and \r\n from the given text.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	public static function stripCrap($text) {
		// strip session links, security tokens and access tokens	
		$text = Regex::compile('(?<=\?|&)([st]=[a-f0-9]{40}|at=\d+-[a-f0-9]{40})')->replace($text, '');
		
		// convert html entities (utf-8)
		$text = Regex::compile('&#(3[2-9]|[4-9][0-9]|\d{3,5});')->replace($text, new Callback(function ($matches) {
			return StringUtil::getCharacter(intval($matches[1]));
		}));
		
		// unify new lines
		$text = StringUtil::unifyNewlines($text);
		
		// remove emoji (MySQL 5.1 does not support them)
		$text = preg_replace('~\xF0\x9F[\x80-\xBF][\x80-\xBF]~', '', $text);
		
		return $text;
	}
	
	/**
	 * Gets mentioned users.
	 * 
	 * @param	string		$text
	 * @return	array<string>
	 */
	public static function getMentionedUsers($text) {
		// remove quotes
		$newText = $text;
		if (preg_match_all("~(?:\[quote|\[/quote\])~i", $text, $matches)) {
			$newText = '';
			$substrings = preg_split("~(?:\[quote|\[/quote\])~i", $text);
			
			$inQuote = 0;
			foreach ($matches[0] as $i => $tag) {
				if (!$inQuote) {
					$newText .= $substrings[$i];
				}
				
				if ($tag == '[quote') {
					$inQuote++;
				}
				else {
					$inQuote--;
				}
			}
			
			if (!$inQuote) $newText .= $substrings[count($substrings) - 1];
		}
		
		// check for mentions
		if (preg_match_all("~\[url='[^']+'\]@(.+?)\[/url\]~", $newText, $matches)) {
			return $matches[1];
		}
		
		return array();
	}
	
	/**
	 * Gets quoted users.
	 *
	 * @param	string		$text
	 * @return	array<string>
	 */
	public static function getQuotedUsers($text) {
		$usernames = array();
		if (preg_match_all("~(?:\[(quote)=(?:')?(.+?)(?:')?(?:,[^\]]*)?\]|\[/quote\])~i", $text, $matches)) {
			$level = 0;
			
			foreach ($matches[1] as $i => $tag) {
				if ($tag == 'quote') {
					if (!$level) {
						$usernames[] = $matches[2][$i];
					}
					$level++;
				}
				else {
					$level--;
				}
			}
		}
		
		return $usernames;
	}
}
