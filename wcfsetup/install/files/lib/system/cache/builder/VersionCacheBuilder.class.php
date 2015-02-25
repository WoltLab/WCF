<?php
namespace wcf\system\cache\builder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

/**
 * Caches the versions for a certain package and object type.
 * 
 * @deprecated	2.1 - will be removed with WCF 2.2
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class VersionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {	
		// get object types
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.versionableObject');
		
		$data = array(
			'versions' => array(),
			'versionIDs' => array()
		);	
		
		foreach ($objectTypes as $objectType) {
			$objectTypeID = $objectType->objectTypeID;
			
			$sql = "SELECT	* 
				FROM	". call_user_func(array($objectType->className, 'getDatabaseVersionTableName'));
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array());
			
			while ($row = $statement->fetchArray()) {
				$databaseIndexName = call_user_func(array($objectType->className, 'getDatabaseTableIndexName'));
				$databaseVersionTableIndexName = call_user_func(array($objectType->className, 'getDatabaseVersionTableIndexName'));
				
				$object = new $objectType->className(null, $row);
				$data['versions'][$objectTypeID][$object->$databaseIndexName][] = $object;
				$data['versionIDs'][$objectTypeID][$object->$databaseIndexName][] = $object->$databaseVersionTableIndexName;
			}
		}
		
		return $data;
	}
}
