<?php
namespace wcf\system\version;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\VersionCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Handles versions of DatabaseObjects.
 * 
 * @deprecated	2.1 - will be removed with WCF 2.2
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.version
 * @category	Community Framework
 */
class VersionHandler extends SingletonFactory {
	/**
	 * cached versions
	 * @var	array<\wcf\data\VersionableDatabaseObject>
	 */
	protected $versions = array();
	
	/**
	 * maps each version id to its object type id and object type version id
	 * @var	array<array>
	 */
	protected $versionIDs = array();
	
	/**
	 * mapes the names of the version object types to the object type ids
	 * @var	array<integer>
	 */
	protected $objectTypeIDs = array();
	
	/**
	 * list of version object types
	 * @var	array<\wcf\data\object\type>
	 */
	protected $objectTypes = array();
	
	/**
	 * Returns all version of object with the given object type id and object id.
	 * 
	 * @param	integer	$objectTypeID
	 * @param	integer	$objectID
	 * @return	array<\wcf\data\VersionableDatabaseObject>
	 */
	public function getVersions($objectTypeID, $objectID) {
		if (isset($this->versions[$objectTypeID][$objectID])) {
			return $this->versions[$objectTypeID][$objectID];
		}
		
		return array();
	}
	
	/**
	 * Returns the database object with the given version id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$versionID
	 * @return	\wcf\data\VersionableDatabaseObject
	 */
	public function getVersionByID($objectTypeID, $versionID) {
		if (isset($this->versionIDs[$objectTypeID][$versionID])) {
			return $this->versionIDs[$objectTypeID][$versionID];
		}
		
		return null;
	}
	
	/**
	 * Returns the object type with the given id.
	 * 
	 * @param	integer	$objectTypeID
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypeIDs[$objectTypeID])) {
			return $this->getObjectTypeByName($this->objectTypeIDs[$objectTypeID]);
		}
		
		return null;
	}
	
	/**
	 * Returns the object type with the given name.
	 * 
	 * @param	string		$objectTypeName
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($objectTypeName) {
		if (isset($this->objectTypes[$objectTypeName])) {
			return $this->objectTypes[$objectTypeName];
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.versionableObject');
		
		foreach ($this->objectTypes as $objectType) {
			$this->objectTypeIDs[$objectType->objectTypeID] = $objectType->objectType;
		}
		
		$this->versions = VersionCacheBuilder::getInstance()->getData(array(), 'versions');
		$this->versionIDs = VersionCacheBuilder::getInstance()->getData(array(), 'versionIDs');
	}
	
	/**
	 * Reloads the version cache.
	 */
	public function reloadCache() {
		VersionCacheBuilder::getInstance()->reset();
		
		$this->init();
	}
	
	/**
	 * Returns a list of object types
	 * 
	 * @return	array<\wcf\data\object\type\ObjectType>
	 */
	public function getObjectTypes() {
		return $this->objectTypes;
	}
}
