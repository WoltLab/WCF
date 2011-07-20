<?php
namespace wcf\system\package;
use wcf\system\cache\CacheHandler;

/**
 * PackageDependencyHandler stores package dependencies and providing a consistent interface for accessing.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
abstract class PackageDependencyHandler {
	/**
	 * cache of package dependencies
	 *
	 * @var	array
	 */	
	protected static $packageDependencyCache = null;
	
	/**
	 * Returns the id of a specific package in the active dependencies.
	 *
	 * @param	string		$package	package identifier
	 * @return	mixed
	 */	
	public static function getPackageID($package) {
		if (!defined('PACKAGE_ID')) {
			return null;
		}
		
		if (self::$packageDependencyCache === null) {
			self::readCache();
		}
		
		if (isset(self::$packageDependencyCache['resolve'][$package])) {
			$packageID = self::$packageDependencyCache['resolve'][$package];
			
			if (is_array($packageID)) {
				$packageID = array_shift($packageID);
			}
			
			return $packageID;
		}
		
		return null;
	}
	
	/**
	 * Returns the package ids of all dependent packages.
	 *
	 * @return	array
	 */	
	public static function getDependencies() {
		if (!defined('PACKAGE_ID')) {
			return null;
		}
		
		if (self::$packageDependencyCache === null) {
			self::readCache();
		}
		
		return self::$packageDependencyCache['dependency'];
	}
	
	/**
	 * Reads package dependency cache.
	 */	
	protected static function readCache() {
		CacheHandler::getInstance()->addResource(
			'packageDependencies-'.PACKAGE_ID,
			WCF_DIR.'cache/cache.packageDependencies-'.PACKAGE_ID.'.php',
			'wcf\system\cache\builder\CacheBuilderPackageDependency'
		);
		
		self::$packageDependencyCache = CacheHandler::getInstance()->get('packageDependencies-'.PACKAGE_ID);
	}
}
