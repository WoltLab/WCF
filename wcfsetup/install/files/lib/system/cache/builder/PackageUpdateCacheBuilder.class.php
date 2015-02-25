<?php
namespace wcf\system\cache\builder;
use wcf\system\package\PackageUpdateDispatcher;

/**
 * Caches the number of outstanding updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class PackageUpdateCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$updates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
		
		return array('updates' => count($updates));
	}
}
