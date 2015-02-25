<?php
namespace wcf\system\cache\builder;
use wcf\data\core\object\CoreObjectList;

/**
 * Caches the core objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CoreObjectCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		$coreObjectList = new CoreObjectList();
		$coreObjectList->readObjects();
		$coreObjects = $coreObjectList->getObjects();
		
		foreach ($coreObjects as $coreObject) {
			$tmp = explode('\\', $coreObject->objectName);
			$className = array_pop($tmp);
			$data[$className] = $coreObject->objectName;
		}
		
		return $data;
	}
}
