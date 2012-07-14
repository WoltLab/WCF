<?php
namespace wcf\system\cache\builder;
use wcf\data\package\PackageList;

/**
 * Caches all registered packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class PackageCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$packageList = new PackageList();
		$packageList->sqlLimit = 0;
		$packageList->readObjects();
		
		return $packageList->getObjects();
	}
}
