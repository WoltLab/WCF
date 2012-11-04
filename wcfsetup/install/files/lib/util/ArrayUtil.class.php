<?php
namespace wcf\util;

/**
 * Contains Array-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
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
	 * Alias to php array_intersect_key() function.
	 * 
	 * @deprecated	as of WCF 2.0, use PHP's array_intersect_key() function directly
	 * @see	array_intersect_key()
	 * 
	 * @param	array		$array1
	 * @param	array		$array2
	 * @return	array
	 */
	public static function intersectKeys($array1, $array2) {
		$parameters = func_get_args();
		return call_user_func_array('array_intersect_key', $parameters);
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
	
	private function __construct() { }
}
