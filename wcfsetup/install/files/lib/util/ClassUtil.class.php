<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Provides methods for class interactions.
 * 
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class ClassUtil {
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
		// validate parameters
		if (!is_string($className)) {
			return false;
		}
		else if (!class_exists($className)) {
			throw new SystemException("Cannot determine class inheritance, class '".$className."' does not exist");
		}
		else if (!class_exists($targetClass) && !interface_exists($targetClass)) {
			throw new SystemException("Cannot determine class inheritance, reference class '".$targetClass."' does not exist");
		}
		
		// check for simple inheritance
		if (class_exists($targetClass)) {
			return is_subclass_of($className, $targetClass);
		}
		
		// check for interface
		$reflectionClass = new \ReflectionClass($className);
		return $reflectionClass->implementsInterface($targetClass);
	}
	
	private function __construct() { }
}
