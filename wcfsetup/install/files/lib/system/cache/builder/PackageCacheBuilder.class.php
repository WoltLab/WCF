<?php
namespace wcf\system\cache\builder;
use wcf\data\package\PackageList;

/**
 * Caches all installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class PackageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [
			'packages' => [],
			'packageIDs' => []
		];
		
		$packageList = new PackageList();
		$packageList->readObjects();
		
		foreach ($packageList as $package) {
			$data['packages'][$package->packageID] = $package;
			$data['packageIDs'][$package->package] = $package->packageID;
		}
		
		return $data;
	}
}
