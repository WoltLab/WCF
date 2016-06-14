<?php
namespace wcf\system\cache\builder;
use wcf\system\package\PackageUpdateDispatcher;

/**
 * Caches the number of outstanding updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class PackageUpdateCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$updates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
		
		return ['updates' => count($updates)];
	}
}
