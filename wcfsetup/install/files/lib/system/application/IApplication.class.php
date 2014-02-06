<?php
namespace wcf\system\application;

/**
 * Default interface for all applications for the community framework.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.application
 * @category	Community Framework
 */
interface IApplication {
	/**
	 * Initializes this application, called after all applications have been loaded.
	 */
	public function __run();
	
	/**
	 * Returns true if current application is treated as active and was invoked directly.
	 * 
	 * @return	boolean
	 */
	public function isActiveApplication();
	
	/**
	 * Returns the qualified name of this application's primary controller.
	 * 
	 * @return	string
	 */
	public function getPrimaryController();
	
	/**
	 * Forwards unknown method calls to WCF.
	 * 
	 * @param	string		$method
	 * @param	array		$arguments
	 * @return	mixed
	 */
	public static function __callStatic($method, array $arguments);
}
