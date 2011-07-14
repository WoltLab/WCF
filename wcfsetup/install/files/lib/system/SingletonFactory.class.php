<?php
namespace wcf\system;

/**
 * Basis class for singleton classes.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
abstract class SingletonFactory {
	/**
	 * list of singletons
	 * @var	array<SingletonFactory>
	 */
	protected static $__singletonObjects = array();
	
	/**
	 * Singletons do not support a public constructor. Override init() if
	 * your class needs to initialize components on creation.
	 */
	protected final function __construct() {
		$this->init();
	}
	
	/**
	 * Called within __construct(), override if neccessary.
	 */	
	protected function init() { }
	
	/**
	 * Object cloning is disallowed.
	 */
	protected final function __clone() { }
	
	/**
	 * Returns an unique instance of current child class.
	 * 
	 * @return	SingletonFactory
	 */
	public static final function getInstance() {
		$className = get_called_class();
		if (!isset(self::$__singletonObjects[$className])) {
			self::$__singletonObjects[$className] = new $className();
		}
		
		return self::$__singletonObjects[$className];
	}
}
?>
