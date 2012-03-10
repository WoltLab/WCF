<?php
namespace wcf\util;

/**
 * Provides methods for class interactions.
 *
 * @author 	Tim Düsterhus, Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class ClassUtil {
	/**
	 * Checks whether the given objects are equal.
	 * Objects are considered equal, when they are instances of the same class and all attributes are equal.
	 *
	 * @param	object		$a
	 * @param	object		$b
	 * @return	boolean
	 */
	public static function equals($a, $b) {
		if (!is_object($a)) return false;
		
		return print_r($a, true) === print_r($b, true);
	}
	
	/**
	 * Checks wether given class extends or implements the target class or interface.
	 * You SHOULD NOT call this method if 'instanceof' satisfies your request!
	 *
	 * @param	string		$className
	 * @param	string		$targetClass
	 * @return	boolean
	 */
	public static function isInstanceOf($className, $targetClass) {
		if (!is_string($className)) return false;
		
		if (class_exists($targetClass)) {
			return is_subclass_of($className, $targetClass);
		}
		else if (interface_exists($targetClass)) {
			$reflectionClass = new \ReflectionClass($className);
			return $reflectionClass->implementsInterface($targetClass);
		}
		
		return false;
	}
}
