<?php
namespace wcf\util;
use wcf\system\Callback;
use wcf\system\Regex;

/**
 * Contains message-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.message
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
		
		return $text;
	}
}
