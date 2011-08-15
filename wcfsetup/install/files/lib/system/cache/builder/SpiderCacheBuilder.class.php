<?php
namespace wcf\system\cache\builder;
use wcf\data\spider\SpiderList;

/**
 * Caches the list of search engine spiders.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class SpiderCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$spiderList = new SpiderList();
		$spiderList->sqlOrderBy = "spider.spiderID ASC";
		$spiderList->sqlLimit = 0;
		$spiderList->readObjects();
		
		return $spiderList->getObjects();
	}
}
