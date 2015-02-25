<?php
namespace wcf\system\cache\builder;
use wcf\data\sitemap\SitemapList;

/**
 * Caches sitemap structure.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class SitemapCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$sitemapList = new SitemapList();
		$sitemapList->sqlOrderBy = "sitemap.showOrder ASC";
		$sitemapList->readObjects();
		
		return $sitemapList->getObjects();
	}
}
