<?php
namespace wcf\system;
use wcf\system\exception\SystemException;

/**
 * Base class for singleton factories.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
	 * Called within __construct(), override if necessary.
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
		$className = static::class;
		if (!isset(self::$__singletonObjects[$className])) {
			// The previously used `null` value forced us into using `array_key_exists()` which has a bad performance,
			// especially with the growing list of derived classes that are used today. Saves a few ms on every request.
			self::$__singletonObjects[$className] = false;
			self::$__singletonObjects[$className] = new $className();
		}
		else if (self::$__singletonObjects[$className] === false) {
			throw new SystemException("Infinite loop detected while trying to retrieve object for '" . $className . "'");
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
