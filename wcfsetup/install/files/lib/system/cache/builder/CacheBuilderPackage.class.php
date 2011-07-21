<?php
namespace wcf\system\cache\builder;
use wcf\data\package\PackageList;
use wcf\system\cache\CacheBuilder;
use wcf\system\WCF;

/**
 * Caches all registered packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderPackage implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$packageList = new PackageList();
		$packageList->sqlLimit = 0;
		$packageList->readObjects();
		
		return $packageList->getObjects();
	}
}
