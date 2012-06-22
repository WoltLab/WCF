<?php
namespace wcf\system\cache\builder;
use wcf\data\sitemap\SitemapList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches sitemap structure.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class SitemapCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$sitemapList = new SitemapList();
		$sitemapList->getConditionBuilder()->add("sitemap.packageID IN (?)", array(PackageDependencyHandler::getInstance()->getPackageIDs()));
		$sitemapList->sqlLimit = 0;
		$sitemapList->readObjects();
		
		return $sitemapList->getObjects();
	}
}
