<?php
namespace wcf\util;

/**
 * Replaces quoted strings in a text.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class StringStack {
	/**
	 * local string stack
	 * @var	array<string>
	 */
	protected static $stringStack = array();
	
	/**
	 * Replaces a string with an unique hash value.
	 * 
	 * @param	string		$string
	 * @param	string		$type
	 * @return	string		$hash
	 */
	public static function pushToStringStack($string, $type = 'default') {
		$hash = '@@'.StringUtil::getHash(uniqid(microtime()).$string).'@@';
		
		if (!isset(self::$stringStack[$type])) {
			self::$stringStack[$type] = array();
		}
		
		self::$stringStack[$type][$hash] = $string;
		
		return $hash;
	}
	
	/**
	 * Reinserts strings that have been replaced by unique hash values.
	 *
	 * @param	string		$string
	 * @param	string		$type
	 * @return	string
	 */
	public static function reinsertStrings($string, $type = 'default') {
		if (isset(self::$stringStack[$type])) {
			foreach (self::$stringStack[$type] as $hash => $value) {
				if (StringUtil::indexOf($string, $hash) !== false) {
					$string = StringUtil::replace($hash, $value, $string);
					unset(self::$stringStack[$type][$hash]);
				}
			}
		}
		
		return $string;
	}
	
	/**
	 * Returns the stack.
	 * 
	 * @param	string		$type
	 * @return	array
	 */
	public static function getStack($type = 'default') {
		if (isset(self::$stringStack[$type])) {
			return self::$stringStack[$type];
		}
		
		return array();
	}
	
	private function __construct() { }
}
