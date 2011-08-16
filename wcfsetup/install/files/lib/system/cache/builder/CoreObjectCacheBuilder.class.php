<?php
namespace wcf\system\cache\builder;
use wcf\data\core\object\CoreObjectList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches the core objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class CoreObjectCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']);
		$data = array();
		
		$coreObjectList = new CoreObjectList();
		$coreObjectList->getConditionBuilder()->add("core_object.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$coreObjectList->sqlLimit = 0;
		$coreObjectList->readObjects();
		$coreObjects = $coreObjectList->getObjects();
		
		foreach ($coreObjects as $coreObject) {
			if (!isset($data[$coreObject->packageID])) {
				$data[$coreObject->packageID] = array();
			}
			
			$tmp = explode('\\', $coreObject->objectName);
			$className = array_pop($tmp);
			$data[$coreObject->packageID][$className] = $coreObject->objectName;
		}
		
		return $data;
	}
}
