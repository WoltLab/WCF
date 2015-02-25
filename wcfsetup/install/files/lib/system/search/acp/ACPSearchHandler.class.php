<?php
namespace wcf\system\search\acp;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ACPSearchProviderCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\ClassUtil;

/**
 * Handles ACP Search.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class ACPSearchHandler extends SingletonFactory {
	/**
	 * list of application abbreviations
	 * @var	array<string>
	 */
	public $abbreviations = array();
	
	/**
	 * list of acp search provider
	 * @var	array<\wcf\data\acp\search\provider\ACPSearchProvider>
	 */
	protected $cache = null;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = ACPSearchProviderCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns a list of search result collections for given query.
	 * 
	 * @param	string		$query
	 * @param	integer		$limit
	 * @return	array<\wcf\system\search\acp\ACPSearchResultList>
	 */
	public function search($query, $limit = 10) {
		$data = array();
		$maxResultsPerProvider = ceil($limit / 2);
		$totalResultCount = 0;
		
		foreach ($this->cache as $acpSearchProvider) {
			$className = $acpSearchProvider->className;
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\search\acp\IACPSearchResultProvider')) {
				throw new SystemException("'".$className."' does not implement 'wcf\system\search\acp\IACPSearchResultProvider'");
			}
			
			$provider = new $className();
			$results = $provider->search($query, $maxResultsPerProvider);
			
			if (!empty($results)) {
				$resultList = new ACPSearchResultList($acpSearchProvider->providerName);
				foreach ($results as $result) {
					$resultList->addResult($result);
				}
				
				// sort list and reduce results
				$resultList->sort();
				$resultList->reduceResultsTo($maxResultsPerProvider);
				
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
		
		// sort all result lists
		foreach ($data as $resultList) {
			$resultList->sort();
		}
		
		return $data;
	}
	
	/**
	 * Returns a list of application abbreviations.
	 * 
	 * @param	string		$suffix
	 * @return	array<string>
	 */
	public function getAbbreviations($suffix = '') {
		if (empty($this->abbreviations)) {
			// append the 'WCF' pseudo application
			$this->abbreviations[] = 'wcf';
			
			// get running application
			$this->abbreviations[] = ApplicationHandler::getInstance()->getAbbreviation(ApplicationHandler::getInstance()->getActiveApplication()->packageID);
			
			// get dependent applications
			foreach (ApplicationHandler::getInstance()->getDependentApplications() as $application) {
				$this->abbreviations[] = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
			}
		}
		
		if (!empty($suffix)) {
			$abbreviations = array();
			foreach ($this->abbreviations as $abbreviation) {
				$abbreviations[] = $abbreviation . $suffix;
			}
			
			return $abbreviations;
		}
		
		return $this->abbreviations;
	}
}
