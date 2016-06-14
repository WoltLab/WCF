<?php
namespace wcf\system\cache\builder;
use wcf\data\spider\SpiderList;

/**
 * Caches the list of search engine spiders.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class SpiderCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$spiderList = new SpiderList();
		$spiderList->sqlOrderBy = "spider.spiderID ASC";
		$spiderList->readObjects();
		
		return $spiderList->getObjects();
	}
}
