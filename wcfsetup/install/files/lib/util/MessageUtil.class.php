<?php
namespace wcf\util;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\Callback;
use wcf\system\Regex;

/**
 * Contains message-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
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
		
		// remove 4 byte utf-8 characters as MySQL < 5.5 does not support them
		// see http://stackoverflow.com/a/16902461/782822
		$text = preg_replace('/[\xF0-\xF7].../s', '', $text);
		
		// remove control characters
		$text = preg_replace('~[\x00-\x08\x0B-\x1F\x7F]~', '', $text);
		
		return $text;
	}
	
	/**
	 * Returns the mentioned users in the given text.
	 * 
	 * @param       HtmlInputProcessor      $htmlInputProcessor     html input processor instance
	 * @return      string[]                mentioned usernames
	 */
	public static function getMentionedUsers(HtmlInputProcessor $htmlInputProcessor) {
		$usernames = [];
		
		$elements = $htmlInputProcessor->getHtmlInputNodeProcessor()->getDocument()->getElementsByTagName('woltlab-mention');
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			if (DOMUtil::hasParent($element, 'blockquote')) {
				// ignore mentions within quotes
				continue;
			}
			
			$usernames[] = $element->getAttribute('data-username');
		}
		
		return $usernames;
	}
	
	/**
	 * Returns the quoted users in the given text.
	 *
	 * @param       HtmlInputProcessor      $htmlInputProcessor     html input processor instance
	 * @return      string[]                quoted usernames
	 */
	public static function getQuotedUsers(HtmlInputProcessor $htmlInputProcessor) {
		$usernames = [];
		
		$elements = $htmlInputProcessor->getHtmlInputNodeProcessor()->getDocument()->getElementsByTagName('blockquote');
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$username = $element->getAttribute('data-author');
			if (!empty($username)) $usernames[] = $username;
		}
		
		return $usernames;
	}
	
	/**
	 * Truncates a formatted message and keeps the HTML syntax intact.
	 * 
	 * @param	string		$message		string which shall be truncated
	 * @param	integer		$maxLength		string length after truncating
	 * @return	string					truncated string
	 */
	public static function truncateFormattedMessage($message, $maxLength = 1000) {
		$message = Regex::compile('<!-- begin:parser_nonessential -->.*?<!-- end:parser_nonessential -->', Regex::DOT_ALL)->replace($message, '');
		return StringUtil::truncateHTML($message, $maxLength);
	}
}
