<?php
namespace wcf\data\object\type;
use wcf\system\cache\CacheHandler;
use wcf\system\SingletonFactory;

/**
 * Manages the object type cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category 	Community Framework
 */
class ObjectTypeCache extends SingletonFactory {
	/**
	 * object type definitions
	 * @var array<wcf\data\object\type\definition\ObjectTypeDefinition>
	 */
	protected $definitions = array();
	
	/**
	 * object type definitions sorted by name
	 * @var array<wcf\data\object\type\definition\ObjectTypeDefinition>
	 */
	protected $definitionsByName = array();
	
	/**
	 * object types
	 * @var array<wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * object types grouped by definition
	 * @var array
	 */
	protected $groupedObjectTypes = array();
	
	/**
	 * @see wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get definition cache
		CacheHandler::getInstance()->addResource('objectType-'.PACKAGE_ID, WBB_DIR.'cache/cache.objectType-'.PACKAGE_ID.'.php', 'wcf\system\cache\builder\ObjectTypeCacheBuilder');
		$this->definitions = CacheHandler::getInstance()->get('objectType-'.PACKAGE_ID, 'definitions');
		foreach ($this->definitions as $definition) {
			$this->definitionsByName[$definition->definitionName] = $definition;
		}
		
		// get object type cache
		$this->objectTypes = CacheHandler::getInstance()->get('objectType-'.PACKAGE_ID, 'objectTypes');
		foreach ($this->objectTypes as $objectType) {
			$definition = $this->getDefinition($objectType->definitionID);
			if (!isset($this->groupedObjectTypes[$definition->definitionName])) {
				$this->groupedObjectTypes[$definition->definitionName] = array();
			}
			
			$this->groupedObjectTypes[$definition->definitionName][$objectType->objectTypeName] = $objectType;
		}
	}
	
	/**
	 * Gets an object type definition by id
	 * 
	 * @param	integer		$definitionID
	 * @return	wcf\data\object\type\definition\ObjectTypeDefinition
	 */
	public function getDefinition($definitionID) {
		if (isset($this->definitions[$definitionID])) {
			return $this->definitions[$definitionID];
		}
		
		return null;
	}
	
	/**
	 * Gets an object type definition by name
	 * 
	 * @param	string		$definitionName
	 * @return	wcf\data\object\type\definition\ObjectTypeDefinition
	 */
	public function getDefinitionByName($definitionName) {
		if (isset($this->definitionsByName[$definitionName])) {
			return $this->definitionsByName[$definitionName];
		}
		
		return null;
	}
	
	/**
	 * Gets an object type by id
	 * 
	 * @param	integer		$objectTypeID
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypes[$objectTypeID])) {
			return $this->objectTypes[$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of object types.
	 * 
	 * @param	string		$definitionName
	 * @return	array<wcf\data\object\type\ObjectType>
	 */
	public function getObjectTypes($definitionName) {
		if (isset($this->groupedObjectTypes[$definitionName])) {
			return $this->groupedObjectTypes[$definitionName];
		}
		
		return array();
	}
	
	/**
	 * Returns an object type.
	 * 
	 * @param	string		$definitionName
	 * @param	string		$objectTypeName
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($definitionName, $objectTypeName) {
		if (isset($this->groupedObjectTypes[$definitionName]) && isset($this->groupedObjectTypes[$definitionName][$typeName])) {
			return $this->groupedObjectTypes[$definitionName][$typeName];
		}
		
		return null;
	}
}
