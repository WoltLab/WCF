<?php
namespace wcf\system;
use wcf\system\exception\SystemException;

/**
 * Represents a regular expression.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
final class Regex {
	/**
	 * delimiter used internally
	 * @var	string
	 */
	const REGEX_DELIMITER = '/';
	
	/**
	 * indicates that no modifier is applied
	 * @var	integer
	 */
	const MODIFIER_NONE = 0;
	
	/**
	 * indicates case insensitive matching
	 * @var	integer
	 */
	const CASE_INSENSITIVE = 1;
	
	/**
	 * indicates ungreedy matching
	 * @var	integer
	 */
	const UNGREEDY = 2;
	
	/**
	 * indicates that no extra time is spent on analysing
	 * @var	integer
	 */
	const NO_ANALYSE = 8;
	
	/**
	 * indicates that whitespaces are igored in regex
	 * @var	integer
	 */
	const IGNORE_WHITESPACE = 16;
	
	/**
	 * indicates that a dot matches every char
	 * @var	integer
	 */
	const DOT_ALL = 32;
	
	/**
	 * indicates that ^/$ match start and end of a line instead of the whole string
	 * @var	integer
	 */
	const MULTILINE = 64;
	
	/**
	 * indicates that pattern string is treated as UTF-8.
	 * @var	integer
	 */
	const UTF_8 = 128;
	
	/**
	 * indicates that no flags are set
	 * @var	integer
	 */
	const FLAGS_NONE = 0;
	
	/**
	 * indicates that default flags are set
	 * @var	integer
	 */
	const FLAGS_DEFAULT = 1;
	
	/**
	 * captures the offset of an match (all excluding replace)
	 * @var	integer
	 */
	const CAPTURE_OFFSET = 2;
	
	/**
	 * indicates default pattern ordering (match all only)
	 * @var	integer
	 */
	const ORDER_MATCH_BY_PATTERN = 4;
	
	/**
	 * indicates alternative set ordering (match all only)
	 * @var	integer
	 */
	const ORDER_MATCH_BY_SET = 8;
	
	/**
	 * indicates that only non-empty pieces will be splitted (split only)
	 * @var	integer
	 */
	const SPLIT_NON_EMPTY_ONLY = 16;
	
	/**
	 * indicates that the split delimiter is returned as well (split only)
	 * @var	integer
	 */
	const CAPTURE_SPLIT_DELIMITER = 32;
	
	/**
	 * compiled regex
	 * @var	string
	 */
	private $regex = '';
	
	/**
	 * last matches
	 * @var	array
	 */
	private $matches = [];
	
	/**
	 * Creates a regex.
	 * 
	 * @param	string		$regex
	 * @param	integer		$modifier
	 * @throws	SystemException
	 */
	public function __construct($regex, $modifier = self::MODIFIER_NONE) {
		// escape delimiter
		$regex = str_replace(self::REGEX_DELIMITER, '\\'.self::REGEX_DELIMITER, $regex);
		
		// add delimiter
		$this->regex = self::REGEX_DELIMITER.$regex.self::REGEX_DELIMITER;
		
		// add modifiers
		if ($modifier & self::CASE_INSENSITIVE) $this->regex .= 'i';
		if ($modifier & self::UNGREEDY) $this->regex .= 'U';
		if (!($modifier & self::NO_ANALYSE)) $this->regex .= 'S';
		if ($modifier & self::IGNORE_WHITESPACE) $this->regex .= 'x';
		if ($modifier & self::DOT_ALL) $this->regex .= 's';
		if ($modifier & self::MULTILINE) $this->regex .= 'm';
		if ($modifier & self::UTF_8) $this->regex .= 'u';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function compile($regex, $modifier = self::MODIFIER_NONE) {
		return new self($regex, $modifier);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __invoke($string) {
		return $this->match($string);
	}
	
	/**
	 * Checks whether the regex is syntactically correct.
	 * 
	 * @return	boolean
	 */
	public function isValid() {
		try {
			$this->match(''); // we don't care about the result, we only care about the exception
			return true;
		}
		catch (SystemException $e) {
			// we have a syntax error now
			return false;
		}
	}
	
	// @codingStandardsIgnoreStart
	/**
	 * Checks whether the regex matches the given string.
	 * 
	 * @param	string		$string		string to match
	 * @param	boolean		$all		indicates if all matches are collected
	 * @param	integer		$flags		match flags
	 * @return	integer				return value of preg_match(_all)
	 */
	public function match($string, $all = false, $flags = self::FLAGS_DEFAULT) {
		$matchFlags = 0;
		if ($flags & self::CAPTURE_OFFSET) $matchFlags |= PREG_OFFSET_CAPTURE;
		
		if ($all) {
			if ($flags & self::FLAGS_DEFAULT) $matchFlags |= PREG_PATTERN_ORDER;
			if (($flags & self::ORDER_MATCH_BY_PATTERN) && !($flags & self::ORDER_MATCH_BY_SET)) $matchFlags |= PREG_PATTERN_ORDER;
			if (($flags & self::ORDER_MATCH_BY_SET) && !($flags & self::ORDER_MATCH_BY_PATTERN)) $matchFlags |= PREG_SET_ORDER;
			
			return $this->checkResult(preg_match_all($this->regex, $string, $this->matches, $matchFlags), 'match');
		}
		
		return $this->checkResult(preg_match($this->regex, $string, $this->matches, $matchFlags), 'match');
	}
	
	/**
	 * Replaces part of the string with the regex.
	 * 
	 * @param	string		$string
	 * @param	mixed		$replacement	replacement-string or instance of wcf\system\Callback
	 * @return	string
	 */
	public function replace($string, $replacement) {
		if ($replacement instanceof Callback) {
			return $this->checkResult(preg_replace_callback($this->regex, $replacement, $string), 'replace');
		}
		
		return $this->checkResult(preg_replace($this->regex, $replacement, $string), 'replace');
	}
	
	/**
	 * Splits the string with the regex.
	 * 
	 * @param	string		$string
	 * @param	integer		$flags
	 * @return	string[]
	 */
	public function split($string, $flags = self::FLAGS_DEFAULT) {
		$splitFlags = 0;
		if ($flags & self::CAPTURE_OFFSET) $splitFlags |= PREG_SPLIT_OFFSET_CAPTURE;
		if ($flags & self::SPLIT_NON_EMPTY_ONLY) $splitFlags |= PREG_SPLIT_NO_EMPTY;
		if ($flags & self::CAPTURE_SPLIT_DELIMITER) $splitFlags |= PREG_SPLIT_DELIM_CAPTURE;
		
		return $this->checkResult(preg_split($this->regex, $string, null, $splitFlags), 'split');
	}
	// @codingStandardsIgnoreEnd
	
	/**
	 * Checks whether there was success.
	 * 
	 * @param	mixed		$result
	 * @param	string		$method
	 * @return	mixed
	 * @throws	SystemException
	 */
	private function checkResult($result, $method = '') {
		if ($result === false || $result === null) {
			switch (preg_last_error()) {
				case PREG_INTERNAL_ERROR:
					$error = 'Internal error';
				break;
				case PREG_BACKTRACK_LIMIT_ERROR:
					$error = 'Backtrack limit was exhausted';
				break;
				case PREG_RECURSION_LIMIT_ERROR:
					$error = 'Recursion limit was exhausted';
				break;
				case PREG_BAD_UTF8_ERROR:
					$error = 'Bad UTF8';
				break;
				case PREG_NO_ERROR:
					return $result;
				break;
				default:
					$error = 'Unknown error';
				break;
			}
			
			throw new SystemException('Could not execute '.($method ? $method.' on ' : '').$this->regex.': '.$error);
		}
		return $result;
	}
	
	/**
	 * Returns the matches of the last string.
	 * 
	 * @return	array
	 */
	public function getMatches() {
		return $this->matches;
	}
	
	/**
	 * Returns the compiled regex.
	 * 
	 * @return	string
	 */
	public function getRegex() {
		return $this->regex;
	}
}
