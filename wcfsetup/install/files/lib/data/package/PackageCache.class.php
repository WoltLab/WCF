<?php
namespace wcf\data\package;
use wcf\system\cache\CacheHandler;
use wcf\system\SingletonFactory;

/**
 * Manages the package cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 */
class PackageCache extends SingletonFactory {
	/**
	 * list of cached packages
	 * @var	array<wcf\data\package\Package>
	 */
	protected $packages = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		CacheHandler::getInstance()->addResource(
			'package',
			WCF_DIR.'cache/cache.package.php',
			'wcf\system\cache\builder\PackageCacheBuilder'
		);
		$this->packages = CacheHandler::getInstance()->get('package');	
	}
	
	/**
	 * Returns a specific package.
	 * 
	 * @param	integer		$packageID
	 * @return	wcf\data\package\Package
	 */
	public function getPackage($packageID) {
		if (isset($this->packages[$packageID])) return $this->packages[$packageID];
		
		return null;
	}
	
	/**
	 * Returns all packages.
	 * 
	 * @return	array<wcf\data\package\Package>
	 */
	public function getPackages() {
		return $this->packages;
	}
}
