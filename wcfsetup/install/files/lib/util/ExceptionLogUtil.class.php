<?php
namespace wcf\util;
use wcf\system\Regex;

/**
 * Contains header-related functions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 * @since	5.2
 */
final class ExceptionLogUtil {
	/**
	 * Splits the given string of Exceptions into an array.
	 * 
	 * @param	string		$contents
	 * @return	string[]
	 */
	public static function splitLog($contents) {
		// unify newlines
		$contents = StringUtil::unifyNewlines($contents);
				
		// split contents
		$split = new Regex('(?:^|\n<<<<\n\n)(?:<<<<<<<<([a-f0-9]{40})<<<<\n|$)');
		$contents = $split->split($contents, Regex::SPLIT_NON_EMPTY_ONLY | Regex::CAPTURE_SPLIT_DELIMITER);
		
		// even items become keys, odd items become values
		return array_merge(...array_map(
			function($v) {
				return [$v[0] => $v[1]];
			},
			array_chunk($contents, 2)
		));
	}
	
	/**
	 * Parses the given log entry.
	 * 
	 * @param	string		$entry
	 * @return	mixed[]
	 */
	public static function parseException($entry) {
		static $regex = null;
		static $chainRegex = null;
		if ($regex === null || $chainRegex === null) {
			$regex = new Regex("(?P<date>[MTWFS][a-z]{2}, \d{1,2} [JFMASOND][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4})\s*\n".
			"Message: (?P<message>.*?)\s*\n".
			"PHP version: (?P<phpVersion>.*?)\s*\n".
			"WoltLab Suite version: (?P<wcfVersion>.*?)\s*\n".
			"Request URI: (?P<requestURI>.*?)\s*\n".
			"Referrer: (?P<referrer>.*?)\s*\n".
			"User Agent: (?P<userAgent>.*?)\s*\n".
			"Peak Memory Usage: (?<peakMemory>\d+)/(?<maxMemory>(?:\d+|-1))\s*\n".
			"(?<chain>======\n".
			".*)", Regex::DOT_ALL);
			$chainRegex = new Regex("======\n".
			"Error Class: (?P<class>.*?)\s*\n".
			"Error Message: (?P<message>.*?)\s*\n".
			"Error Code: (?P<code>[a-zA-Z0-9]+)\s*\n".
			"File: (?P<file>.*?) \((?P<line>\d+)\)\s*\n".
			"Extra Information: (?P<information>(?:-|[a-zA-Z0-9+/]+={0,2}))\s*\n".
			"Stack Trace: (?P<stack>\[[^\n]+\])", Regex::DOT_ALL);
		}
		
		if (!$regex->match($entry)) {
			throw new \InvalidArgumentException('The given entry is malformed');
		}
		$matches = $regex->getMatches();
		$chainRegex->match($matches['chain'], true, Regex::ORDER_MATCH_BY_SET);
		
		$chainMatches = array_map(function ($item) {
			if ($item['information'] === '-') {
				$item['information'] = null;
			}
			else {
				$item['information'] = unserialize(base64_decode($item['information']), ['allowed_classes' => false]);
			}
			
			$item['stack'] = JSON::decode($item['stack']);
			
			return $item;
		}, $chainRegex->getMatches());
		
		$matches['date'] = strtotime($matches['date']);
		$matches['chain'] = $chainMatches;
		
		return $matches;
	}
	
	/**
	 * Forbid creation of ExceptionLogUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
