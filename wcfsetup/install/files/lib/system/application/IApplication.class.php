<?php
namespace wcf\system\application;

/**
 * Default interface for all applications for the community framework.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.application
 * @category 	Community Framework
 */
interface IApplication {
	/**
	 * Forwards unknown method calls to WCF.
	 * 
	 * @param	string		$method
	 * @param	array		$arguments
	 * @return	mixed
	 */
	public static function __callStatic($method, array $arguments);
	
	/**
	 * Forces a reset of all application's cache files.
	 */
	public function resetCache();
}
