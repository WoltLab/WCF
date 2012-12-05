<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\search\provider\ACPSearchProviderList;

/**
 * Caches the ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ACPSearchProviderCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$providerList = new ACPSearchProviderList();
		$providerList->sqlLimit = 0;
		$providerList->sqlOrderBy = "acp_search_provider.showOrder ASC";
		$providerList->readObjects();
		
		return $providerList->getObjects();
	}
}
