<?php
namespace wcf\system\cache\builder;
use wcf\data\sitemap\SitemapList;

/**
 * Caches sitemap structure.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class SitemapCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$sitemapList = new SitemapList();
		$sitemapList->sqlLimit = 0;
		$sitemapList->sqlOrderBy = "sitemap.showOrder ASC";
		$sitemapList->readObjects();
		
		return $sitemapList->getObjects();
	}
}
