<?php
namespace wcf\data\object\type;
use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\system\cache\builder\ObjectTypeCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the object type cache.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category	Community Framework
 */
class ObjectTypeCache extends SingletonFactory {
	/**
	 * object type definitions
	 * @var	ObjectTypeDefinition[]
	 */
	protected $definitions = array();
	
	/**
	 * object type definition ids grouped by category name
	 * @var	integer[][]
	 */
	protected $definitionsByCategory = array();
	
	/**
	 * object type definitions sorted by name
	 * @var	ObjectTypeDefinition[]
	 */
	protected $definitionsByName = array();
	
	/**
	 * object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = array();
	
	/**
	 * object types grouped by definition
	 * @var	array
	 */
	protected $groupedObjectTypes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get definition cache
		$this->definitionsByCategory = ObjectTypeCacheBuilder::getInstance()->getData(array(), 'categories');
		$this->definitions = ObjectTypeCacheBuilder::getInstance()->getData(array(), 'definitions');
		foreach ($this->definitions as $definition) {
			$this->definitionsByName[$definition->definitionName] = $definition;
		}
		
		// get object type cache
		$this->objectTypes = ObjectTypeCacheBuilder::getInstance()->getData(array(), 'objectTypes');
		$this->groupedObjectTypes = ObjectTypeCacheBuilder::getInstance()->getData(array(), 'groupedObjectTypes');
	}
	
	/**
	 * Returns the object type definition with the given id or null if no such
	 * object type definition exists.
	 * 
	 * @param	integer		$definitionID
	 * @return	\wcf\data\object\type\definition\ObjectTypeDefinition
	 */
	public function getDefinition($definitionID) {
		if (isset($this->definitions[$definitionID])) {
			return $this->definitions[$definitionID];
		}
		
		return null;
	}
	
	/**
	 * Returns the object type definition with the given name or null if no
	 * such object type definition exists.
	 * 
	 * @param	string		$definitionName
	 * @return	\wcf\data\object\type\definition\ObjectTypeDefinition
	 */
	public function getDefinitionByName($definitionName) {
		if (isset($this->definitionsByName[$definitionName])) {
			return $this->definitionsByName[$definitionName];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of definitions by category name or 'null' if the given
	 * category name is invalid.
	 * 
	 * @param	string		$categoryName
	 * @return	ObjectTypeDefinition[]
	 */
	public function getDefinitionsByCategory($categoryName) {
		if (isset($this->definitionsByCategory[$categoryName])) {
			$definitions = array();
			foreach ($this->definitionsByCategory[$categoryName] as $definitionID) {
				$definitions[$definitionID] = $this->getDefinition($definitionID);
			}
			
			return $definitions;
		}
		
		return null;
	}
	
	/**
	 * Returns the object type with the given id or null if no such object type
	 * exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypes[$objectTypeID])) {
			return $this->objectTypes[$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns the list of object type with the given definition name.
	 * 
	 * @param	string		$definitionName
	 * @return	ObjectType[]
	 */
	public function getObjectTypes($definitionName) {
		if (isset($this->groupedObjectTypes[$definitionName])) {
			return $this->groupedObjectTypes[$definitionName];
		}
		
		return array();
	}
	
	/**
	 * Returns the object type with the given definition name and given name
	 * or null of no such object type exists.
	 * 
	 * @param	string		$definitionName
	 * @param	string		$objectTypeName
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($definitionName, $objectTypeName) {
		if (isset($this->groupedObjectTypes[$definitionName]) && isset($this->groupedObjectTypes[$definitionName][$objectTypeName])) {
			return $this->groupedObjectTypes[$definitionName][$objectTypeName];
		}
		
		return null;
	}
	
	/**
	 * Returns the object type id with the given definition name and given name.
	 * 
	 * @param	string		$definitionName
	 * @param	string		$objectTypeName
	 * @return	integer
	 */
	public function getObjectTypeIDByName($definitionName, $objectTypeName) {
		$objectType = $this->getObjectTypeByName($definitionName, $objectTypeName);
		if ($objectType !== null) return $objectType->objectTypeID;
		
		return null;
	}
	
	/**
	 * Resets and reloads the object type cache.
	 */
	public function resetCache() {
		ObjectTypeCacheBuilder::getInstance()->reset();
		$this->init();
	}
}
