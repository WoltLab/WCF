<?php
declare(strict_types=1);
namespace wcf\system\application;

/**
 * Default interface for all applications for the WoltLab Suite.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Application
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
	 * Returns the timestamp at which the evaluation period ends for this application. The
	 * special value `0` indicates that there is no active evaluation period at this time.
	 * 
	 * @return      integer
	 */
	public function getEvaluationEndDate();
	
	/**
	 * Returns the id of the WoltLab Plugin-Store file where this app is for purchase. The
	 * special value `0` indicates that there is no such file or it is a WoltLab app.
	 * 
	 * @return      integer
	 */
	public function getEvaluationPluginStoreID();
	
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
