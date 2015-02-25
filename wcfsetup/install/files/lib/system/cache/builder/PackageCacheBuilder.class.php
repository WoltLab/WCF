<?php
namespace wcf\system\cache\builder;
use wcf\data\package\PackageList;

/**
 * Caches all installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class PackageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array(
			'packages' => array(),
			'packageIDs' => array()
		);
		
		$packageList = new PackageList();
		$packageList->readObjects();
		
		foreach ($packageList as $package) {
			$data['packages'][$package->packageID] = $package;
			$data['packageIDs'][$package->package] = $package->packageID;
		}
		
		return $data;
	}
}
