<?php
namespace wcf\system\cache\builder;
use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\system\WCF;

/**
 * Caches object types and object type definitions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class ObjectTypeCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']);
		$data = array(
			'definitions' => array(),
			'objectTypes' => array()
		);
	
		// get definitions
		$sql = "SELECT		object_type_definition.*
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_object_type_definition object_type_definition
			WHERE		object_type_definition.packageID = package_dependency.dependency
					AND package_dependency.packageID = ?
			ORDER BY	package_dependency.priority";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		while ($row = $statement->fetchArray()) {
			$data['definitions'][$row['definitionID']] = new ObjectTypeDefinition(null, $row);
		}

		// get object types
		$sql = "SELECT		object_type.*
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_object_type object_type
			WHERE		object_type.packageID = package_dependency.dependency
					AND package_dependency.packageID = ?
			ORDER BY	package_dependency.priority";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		while ($row = $statement->fetchArray()) {
			$data['objectTypes'][$row['objectTypeID']] = new ObjectType(null, $row);
		}
		
		return $data;
	}
}
