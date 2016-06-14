<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\Callback;

/**
 * Contains Array-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class ArrayUtil {
	/**
	 * Applies StringUtil::trim() to all elements of the given array.
	 * 
	 * @param	array		$array
	 * @param	boolean		$removeEmptyElements
	 * @return	array
	 */
	public static function trim($array, $removeEmptyElements = true) {
		if (!is_array($array)) {
			return StringUtil::trim($array);
		}
		else {
			foreach ($array as $key => $val) {
				$temp = self::trim($val, $removeEmptyElements);
				if ($removeEmptyElements && empty($temp)) unset($array[$key]);
				else $array[$key] = $temp;
			}
			return $array;
		}
	}
	
	/**
	 * Applies intval() to all elements of the given array.
	 * 
	 * @param	array		$array
	 * @return	array
	 */
	public static function toIntegerArray($array) {
		if (!is_array($array)) {
			return intval($array);
		}
		else {
			foreach ($array as $key => $val) {
				$array[$key] = self::toIntegerArray($val);
			}
			return $array;
		}
	}
	
	/**
	 * Converts html special characters in the given array.
	 * 
	 * @param	array		$array
	 * @return	array
	 */
	public static function encodeHTML($array) {
		if (!is_array($array)) {
			return StringUtil::encodeHTML($array);
		}
		else {
			foreach ($array as $key => $val) {
				$array[$key] = self::encodeHTML($val);
			}
			return $array;
		}
	}
	
	/**
	 * Applies stripslashes on all elements of the given array.
	 * 
	 * @param	array		$array
	 * @return	array
	 */
	public static function stripslashes($array) {
		if (!is_array($array)) {
			return stripslashes($array);
		}
		else {
			foreach ($array as $key => $val) {
				$array[$key] = self::stripslashes($val);
			}
			return $array;
		}
	}
	
	/**
	 * Appends a suffix to all elements of the given array.
	 * 
	 * @param	array		$array
	 * @param	string		$suffix
	 * @return	array
	 */
	public static function appendSuffix($array, $suffix) {
		foreach ($array as $key => $value) {
			$array[$key] = $value . $suffix;
		}
		
		return $array;
	}
	
	/**
	 * Converts dos to unix newlines.
	 * 
	 * @param	array		$array
	 * @return	array
	 */
	public static function unifyNewlines($array) {
		if (!is_array($array)) {
			return StringUtil::unifyNewlines($array);
		}
		else {
			foreach ($array as $key => $val) {
				$array[$key] = self::unifyNewlines($val);
			}
			return $array;
		}
	}
	
	/**
	 * Converts a array of strings to requested character encoding.
	 * @see	mb_convert_encoding()
	 * 
	 * @param	string		$inCharset
	 * @param	string		$outCharset
	 * @param	array		$array
	 * @return	string
	 */
	public static function convertEncoding($inCharset, $outCharset, $array) {
		if (!is_array($array)) {
			return StringUtil::convertEncoding($inCharset, $outCharset, $array);
		}
		else {
			foreach ($array as $key => $val) {
				$array[$key] = self::convertEncoding($inCharset, $outCharset, $val);
			}
			return $array;
		}
	}
	
	/**
	 * Returns true when array1 has the same values as array2.
	 * 
	 * @param	array		$array1
	 * @param	array		$array2
	 * @param	callable	$callback
	 * @return	boolean
	 */
	public static function compare(array $array1, array $array2, Callback $callback = null) {
		return static::compareHelper('value', $array1, $array2, $callback);
	}
	
	/**
	 * Returns true when array1 has the same keys as array2.
	 * 
	 * @param	array		$array1
	 * @param	array		$array2
	 * @param	callable	$callback
	 * @return	boolean
	 */
	public static function compareKey(array $array1, array $array2, Callback $callback = null) {
		return static::compareHelper('key', $array1, $array2, $callback);
	}
	
	/**
	 * Compares array1 with array2 and returns true when they are identical.
	 * 
	 * @param	array		$array1
	 * @param	array		$array2
	 * @param	callable	$callback
	 * @return	boolean
	 */
	public static function compareAssoc(array $array1, array $array2, Callback $callback = null) {
		return static::compareHelper('assoc', $array1, $array2, $callback);
	}
	
	/**
	 * Does the actual comparison of the above compare methods.
	 * 
	 * @param	string		$method
	 * @param	array		$array1
	 * @param	array		$array2
	 * @param	Callback	$callback
	 * @return	boolean
	 * @throws	SystemException
	 */
	protected static function compareHelper($method, array $array1, array $array2, Callback $callback = null) {
		// get function name
		$function = null;
		if ($method === 'value') {
			$function = ($callback === null) ? 'array_diff' : 'array_udiff';
		}
		else if ($method === 'key') {
			$function = ($callback === null) ? 'array_diff_key' : 'array_diff_ukey';
		}
		else if ($method === 'assoc') {
			$function = ($callback === null) ? 'array_diff_assoc' : 'array_diff_uassoc';
		}
		
		// check function name
		if ($function === null) {
			throw new SystemException('Unknown comparison method '.$method);
		}
		
		// get parameters
		$params1 = [$array1, $array2];
		$params2 = [$array2, $array1];
		if ($callback !== null) {
			$params1[] = $callback;
			$params2[] = $callback;
		}
		
		// compare the arrays
		return ((count(call_user_func_array($function, $params1)) === 0) && (count(call_user_func_array($function, $params2)) === 0));
	}
	
	/**
	 * Forbid creation of ArrayUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
