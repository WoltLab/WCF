<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\search\provider\ACPSearchProviderList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches the ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class ACPSearchProviderCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$providerList = new ACPSearchProviderList();
		$providerList->getConditionBuilder()->add("acp_search_provider.packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		$providerList->sqlLimit = 0;
		$providerList->sqlOrderBy = "acp_search_provider.showOrder ASC";
		$providerList->readObjects();
		
		return $providerList->getObjects();
	}
}
