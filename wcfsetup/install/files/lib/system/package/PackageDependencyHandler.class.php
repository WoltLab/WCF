<?php
namespace wcf\system\package;
use wcf\system\cache\CacheHandler;
use wcf\system\SingletonFactory;

/**
 * Handles the package dependencies.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class PackageDependencyHandler extends SingletonFactory {
	/**
	 * cache of package dependencies
	 * @var	array
	 */
	protected $packageDependencyCache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$cacheName = 'packageDependencies-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\PackageDependencyCacheBuilder'
		);
		
		$this->packageDependencyCache = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns the id of a specific package in the active dependencies.
	 * 
	 * @param	string		$package	package identifier
	 * @return	mixed
	 */
	public function getPackageID($package) {
		if (!defined('PACKAGE_ID')) {
			return null;
		}
		
		if (isset($this->packageDependencyCache['resolve'][$package])) {
			$packageID = $this->packageDependencyCache['resolve'][$package];
			
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
	public function getDependencies() {
		if (!defined('PACKAGE_ID')) {
			return null;
		}
		
		return $this->packageDependencyCache['dependency'];
	}
}
