<?php
namespace wcf\data\package;
use wcf\system\cache\builder\PackageCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the package cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 */
class PackageCache extends SingletonFactory {
	/**
	 * list of cached packages
	 * @var	array<array>
	 */
	protected $packages = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->packages = PackageCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns a specific package.
	 * 
	 * @param	integer		$packageID
	 * @return	\wcf\data\package\Package
	 */
	public function getPackage($packageID) {
		if (isset($this->packages['packages'][$packageID])) {
			return $this->packages['packages'][$packageID];
		}
		
		return null;
	}
	
	/**
	 * Returns the id of a specific package or 'null' if not found.
	 * 
	 * @param	string		$package
	 * @return	string
	 */
	public function getPackageID($package) {
		if (isset($this->packages['packageIDs'][$package])) {
			return $this->packages['packageIDs'][$package];
		}
		
		return null;
	}
	
	/**
	 * Returns all packages.
	 * 
	 * @return	array<\wcf\data\package\Package>
	 */
	public function getPackages() {
		return $this->packages;
	}
	
	/**
	 * Returns a specific package.
	 * 
	 * @param	string		$package
	 * @return	\wcf\data\package\Package
	 */
	public function getPackageByIdentifier($package) {
		$packageID = $this->getPackageID($package);
		if ($packageID === null) return null;
		
		return $this->getPackage($packageID);
	}
}
