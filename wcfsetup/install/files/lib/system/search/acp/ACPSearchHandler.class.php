<?php
namespace wcf\system\search\acp;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\ClassUtil;

/**
 * Handles ACP Search.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category 	Community Framework
 */
class ACPSearchHandler extends SingletonFactory {
	/**
	 * list of acp search provider
	 * @var	array<wcf\data\acp\search\provider\ACPSearchProvider>
	 */
	protected $cache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$application = ApplicationHandler::getInstance()->getPrimaryApplication();
		$cacheName = 'acpSearchProvider-'.$application->packageID;
		
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\ACPSearchProviderCacheBuilder'
		);
		
		$this->cache = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns a list of search result collections for given query.
	 * 
	 * @param	string		$query
	 * @param	integer		$limit
	 * @return	array<wcf\system\search\acp\ACPSearchResultList>
	 */
	public function search($query, $limit = 10) {
		$data = array();
		$maxResultsPerProvider = ceil($limit / 2);
		$totalResultCount = 0;
		
		foreach ($this->cache as $acpSearchProvider) {
			$className = $acpSearchProvider->className;
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\search\acp\IACPSearchProvider')) {
				throw new SystemException("Class '".$className."' does not implement the interface 'wcf\system\search\acp\IACPSearchProvider'");
			}
			
			$provider = new $className();
			$results = $provider->search($query, $maxResultsPerProvider);
			
			if (!empty($results)) {
				$resultList = new ACPSearchResultList();
				foreach ($results as $result) {
					$resultList->addResult($result);
				}
				
				$data[] = $resultList;
				$totalResultCount += count($resultList);
			}
		}
		
		// reduce results per collection until we match the limit
		while ($totalResultCount > $limit) {
			// calculate highest value
			$max = 0;
			foreach ($data as $resultList) {
				$max = max($max, count($resultList));
			}
			
			// remove one result per result list with hits the $max value
			foreach ($data as $index => $resultList) {
				// break if we hit the $limit during reduction
				if ($totalResultCount == $limit) {
					break;
				}
				
				$count = count($resultList);
				if ($count == $max) {
					$resultList->reduceResults(1);
					$totalResultCount--;
					
					// the last element of this result was removed
					if ($count == 1) {
						unset($data[$index]);
					}
				}
			}
		}
		
		return $data;
	}
}
