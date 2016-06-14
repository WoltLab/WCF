<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\search\provider\ACPSearchProviderList;

/**
 * Caches the ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class ACPSearchProviderCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$providerList = new ACPSearchProviderList();
		$providerList->sqlOrderBy = "acp_search_provider.showOrder ASC";
		$providerList->readObjects();
		
		return $providerList->getObjects();
	}
}
