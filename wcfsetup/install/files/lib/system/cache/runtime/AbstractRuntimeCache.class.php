<?php
namespace wcf\system\cache\runtime;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a runtime cache.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 */
abstract class AbstractRuntimeCache extends SingletonFactory implements IRuntimeCache {
	/**
	 * name of the DatabaseObjectList class
	 * @var	string
	 */
	protected $listClassName = '';
	
	/**
	 * ids of objects which will be fetched next
	 * @var	integer[]
	 */
	protected $objectIDs = [];
	
	/**
	 * cached DatabaseObject objects
	 * @var	DatabaseObject[]
	 */
	protected $objects = [];
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjectID($objectID) {
		$this->cacheObjectIDs([$objectID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjectIDs(array $objectIDs) {
		foreach ($objectIDs as $objectID) {
			if (!array_key_exists($objectID, $this->objects) && !isset($this->objectIDs[$objectID])) {
				$this->objectIDs[$objectID] = $objectID;
			}
		}
	}
	
	/**
	 * Fetches the objects for the pending object ids.
	 */
	protected function fetchObjects() {
		$objectList = $this->getObjectList();
		$objectList->setObjectIDs(array_values($this->objectIDs));
		$objectList->readObjects();
		$this->objects += $objectList->getObjects();
		
		// create null entries for non-existing objects
		foreach ($this->objectIDs as $objectID) {
			if (!array_key_exists($objectID, $this->objects)) {
				$this->objects[$objectID] = null;
			}
		}
		
		$this->objectIDs = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCachedObjects() {
		return $this->objects;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObject($objectID) {
		if (array_key_exists($objectID, $this->objects)) {
			return $this->objects[$objectID];
		}
		
		$this->cacheObjectID($objectID);
		
		$this->fetchObjects();
		
		return $this->objects[$objectID];
	}
	
	/**
	 * Returns a database object list object to fetch cached objects.
	 * 
	 * @return	DatabaseObjectList
	 */
	protected function getObjectList() {
		return new $this->listClassName;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjects(array $objectIDs) {
		$objects = [];
		
		// set already cached objects
		foreach ($objectIDs as $key => $objectID) {
			if (array_key_exists($objectID, $this->objects)) {
				$objects[$objectID] = $this->objects[$objectID];
				unset($objectIDs[$key]);
			}
		}
		
		if (!empty($objectIDs)) {
			$this->cacheObjectIDs($objectIDs);
			
			$this->fetchObjects();
			
			// set newly loaded cached objects
			foreach ($objectIDs as $objectID) {
				$objects[$objectID] = $this->objects[$objectID];
			}
		}
		
		return $objects;
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeObject($objectID) {
		$this->removeObjects([$objectID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeObjects(array $objectIDs) {
		foreach ($objectIDs as $objectID) {
			unset($this->objects[$objectID]);
		}
	}
}
