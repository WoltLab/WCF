<?php
namespace wcf\system\cache\builder;
use wcf\data\package\PackageList;

/**
 * Caches all registered packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class PackageCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$data = array(
			'packages' => array(),
			'packageIDs' => array()
		);
		
		$packageList = new PackageList();
		$packageList->sqlLimit = 0;
		$packageList->readObjects();
		
		foreach ($packageList as $package) {
			$data['packages'][$package->packageID] = $package;
			$data['packageIDs'][$package->package] = $package->packageID;
		}
		
		return $data;
	}
}
