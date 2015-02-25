<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\search\provider\ACPSearchProviderList;

/**
 * Caches the ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ACPSearchProviderCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$providerList = new ACPSearchProviderList();
		$providerList->sqlOrderBy = "acp_search_provider.showOrder ASC";
		$providerList->readObjects();
		
		return $providerList->getObjects();
	}
}
