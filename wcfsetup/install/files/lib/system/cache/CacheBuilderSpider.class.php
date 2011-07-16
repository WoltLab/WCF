<?php
namespace wcf\system\cache;
use wcf\data\spider\SpiderList;

/**
 * Caches the list of search engine spiders.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderSpider implements CacheBuilder {
	/**
	 * @see wcf\system\cache\CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$spiderList = new SpiderList();
		$spiderList->sqlOrderBy = "spider.spiderID ASC";
		$spiderList->sqlLimit = 0;
		$spiderList->readObjects();
		
		return $spiderList->getObjects();
	}
}
