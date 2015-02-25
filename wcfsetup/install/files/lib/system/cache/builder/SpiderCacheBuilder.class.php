<?php
namespace wcf\system\cache\builder;
use wcf\data\spider\SpiderList;

/**
 * Caches the list of search engine spiders.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class SpiderCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$spiderList = new SpiderList();
		$spiderList->sqlOrderBy = "spider.spiderID ASC";
		$spiderList->readObjects();
		
		return $spiderList->getObjects();
	}
}
