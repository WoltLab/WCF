<?php
namespace wcf\system\cache\builder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

/**
 * Caches the versions for a certain package and object type.
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class VersionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {	
		// get object types
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.versionableObject');
		
		$data = array(
			'versions' => array(),
			'versionIDs' => array()
		);	
		
		foreach ($objectTypes as $objectTypeID => $objectType) {
			$processorObject = $objectType->getProcessor();
			
			$sql = "SELECT	* 
				FROM	".$processorObject::getDatabaseVersionTableName();
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array());
			
			while ($row = $statement->fetchArray()) {
				$object = new $objectType->className(null, $row);
				$data['versions'][$objectTypeID][$object->{$processorObject::getDatabaseIndexName()}] = $object;
				$data['versionIDs'][$objectTypeID][$object->{$processorObject::getDatabaseIndexName()}][] = $object->{$processorObject::getDatabaseVersionTableIndexName()};
			}
		}
		
		return $data;
	}
}
