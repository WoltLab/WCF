<?php
namespace wcf\system;
use \wcf\system\exception\SystemException;

/**
 * Represents a regex.
 * 
 * @author	Tim Düsterhus
 * @copyright	2011 - 2012 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
final class Regex {
	/**
	 * The delimiter that is used internally.
	 * 
	 * @var	string
	 */
	const REGEX_DELIMITER = '/';
	
	/**
	 * Do not apply any modifiers.
	 * 
	 * @var	integer
	 */
	const MODIFIER_NONE = 0;
	
	/**
	 * Case insensitive matching.
	 * 
	 * @var	integer
	 */
	const CASE_INSENSITIVE = 1;
	
	/**
	 * Ungreedy matching.
	 * 
	 * @var	integer
	 */
	const UNGREEDY = 2;
	
	/**
	 * eval() replacement of  Regex::replace()
	 *
	 * @var	integer
	 */
	const EVAL_REPLACEMENT = 4;
	
	/**
	 * Do not spend extra time on analysing.
	 * 
	 * @var	integer
	 */
	const NO_ANALYSE = 8;
	
	/**
	 * Ignore whitepsace in regex.
	 *
	 * @var	integer
	 */
	const IGNORE_WHITESPACE = 16;
	
	/**
	 * A dot matches every char.
	 *
	 * @var	integer
	 */
	const DOT_ALL = 32;
	
	/**
	 * The compiled regex (:D)
	 *
	 * @var	string
	 */
	private $regex = '';
	
	/**
	 * The last matches
	 *
	 * @var	array
	 */
	private $matches = array();
	
	/**
	 * Creates a regex.
	 *
	 * @param	string	$regex
	 * @param	integer	$modifier
	 */
	public function __construct($regex, $modifier = self::MODIFIER_NONE) {
		// escape delimiter
		$regex = str_replace(self::REGEX_DELIMITER, '\\'.self::REGEX_DELIMITER, $regex);
		
		// add delimiter
		$this->regex = self::REGEX_DELIMITER.$regex.self::REGEX_DELIMITER;
		
		// add modifiers
		if ($modifier & self::CASE_INSENSITIVE) $this->regex .= 'i';
		if ($modifier & self::UNGREEDY) $this->regex .= 'U';
		if ($modifier & self::EVAL_REPLACEMENT) $this->regex .= 'e';
		if (~$modifier & self::NO_ANALYSE) $this->regex .= 'S';
		if ($modifier & self::IGNORE_WHITESPACE) $this->regex .= 'x';
		if ($modifier & self::DOT_ALL) $this->regex .= 's';
	}
	
	/**
	 * @see	Regex::__construct()
	 */
	public static function compile($regex, $modifier = self::MODIFIER_NONE) {
		return new self($regex, $modifier);
	}
	
	/**
	 * @see	Regex::match()
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
	
	/**
	 * Checks whether the regex matches the given string.
	 * 
	 * @param	string	$string		String to match.
	 * @param	boolean	$all		Find all matches.
	 * @return	integer			Return value of preg_match(_all)
	 */
	public function match($string, $all = false) {
		if ($all) {
			return $this->checkResult(preg_match_all($this->regex, $string, $this->matches), 'match');
		}
		
		return $this->checkResult(preg_match($this->regex, $string, $this->matches), 'match');
	}
	
	/**
	 * Replaces part of the string with the regex.
	 *
	 * @param	string	$string		String to work on.
	 * @param	mixed	$replacement	Either replacement-string or instance of \wcf\system\Callback
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
	 * @param	string	$string		String to split.
	 * @return	array<string>
	 */
	public function split($string) {
		return $this->checkResult(preg_split($this->regex, $string), 'split');
	}
	
	/**
	 * Checks whether there was success.
	 *
	 * @param	mixed	$result
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
