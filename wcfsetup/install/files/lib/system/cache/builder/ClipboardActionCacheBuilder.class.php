<?php
namespace wcf\system\cache\builder;
use wcf\data\clipboard\action\ClipboardActionList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ClipboardActionCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$actionList = new ClipboardActionList();
		$actionList->getConditionBuilder()->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		$actionList->sqlLimit = 0;
		$actionList->readObjects();
		
		return $actionList->getObjects();
	}
}
