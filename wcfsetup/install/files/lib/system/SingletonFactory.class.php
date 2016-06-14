<?php
namespace wcf\system;
use wcf\system\exception\SystemException;

/**
 * Base class for singleton factories.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
abstract class SingletonFactory {
	/**
	 * list of singletons
	 * @var	SingletonFactory[]
	 */
	protected static $__singletonObjects = [];
	
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
	 * Object serializing is disallowed.
	 */
	public final function __sleep() {
		throw new SystemException('Serializing of Singletons is not allowed');
	}
	
	/**
	 * Returns an unique instance of current child class.
	 *
	 * @return	static
	 * @throws	SystemException
	 */
	public static final function getInstance() {
		$className = get_called_class();
		if (!array_key_exists($className, self::$__singletonObjects)) {
			self::$__singletonObjects[$className] = null;
			self::$__singletonObjects[$className] = new $className();
		}
		else if (self::$__singletonObjects[$className] === null) {
			throw new SystemException("Infinite loop detected while trying to retrieve object for '".$className."'");
		}
		
		return self::$__singletonObjects[$className];
	}
	
	/**
	 * Returns whether this singleton is already initialized.
	 *
	 * @return	boolean
	 */
	public static final function isInitialized() {
		$className = get_called_class();
		
		return isset(self::$__singletonObjects[$className]);
	}
}
