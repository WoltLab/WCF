<?php
namespace wcf\system;
use wcf\system\exception\SystemException;

/**
 * Basis class for singleton classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
abstract class SingletonFactory {
	/**
	 * Singletons do not support a public constructor. Override init() if
	 * your class needs to initialize components on creation.
	 */
	public final function __construct() {
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
	 */
	public static final function getInstance() {
		return WCF::getDIContainer()->get(get_called_class());
	}
}
