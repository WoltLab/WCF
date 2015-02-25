<?php
namespace wcf\system\clipboard;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ClipboardActionCacheBuilder;
use wcf\system\cache\builder\ClipboardPageCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Handles clipboard-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard
 * @category	Community Framework
 */
class ClipboardHandler extends SingletonFactory {
	/**
	 * cached list of actions
	 * @var	array
	 */
	protected $actionCache = null;
	
	/**
	 * cached list of clipboard item types
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * list of marked items
	 * @var	array<array>
	 */
	protected $markedItems = null;
	
	/**
	 * cached list of page actions
	 * @var	array
	 */
	protected $pageCache = null;
	
	/**
	 * page object id
	 * @var	integer
	 */
	protected $pageObjectID = 0;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = array(
			'objectTypes' => array(),
			'objectTypeNames' => array()
		);
		$cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.clipboardItem');
		foreach ($cache as $objectType) {
			$this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
			$this->cache['objectTypeNames'][$objectType->objectType] = $objectType->objectTypeID;
		}
		
		$this->pageCache = ClipboardPageCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Loads action cache.
	 */
	protected function loadActionCache() {
		if ($this->actionCache !== null) return;
		
		$this->actionCache = ClipboardActionCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Marks objects as marked.
	 * 
	 * @param	array		$objectIDs
	 * @param	integer		$objectTypeID
	 */
	public function mark(array $objectIDs, $objectTypeID) {
		// remove existing entries first, prevents conflict with INSERT
		$this->unmark($objectIDs, $objectTypeID);
		
		$sql = "INSERT INTO	wcf".WCF_N."_clipboard_item
					(objectTypeID, userID, objectID)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($objectIDs as $objectID) {
			$statement->execute(array(
				$objectTypeID,
				WCF::getUser()->userID,
				$objectID
			));
		}
	}
	
	/**
	 * Removes an object marking.
	 * 
	 * @param	array		$objectIDs
	 * @param	integer		$objectTypeID
	 */
	public function unmark(array $objectIDs, $objectTypeID) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		$conditions->add("userID = ?", array(WCF::getUser()->userID));
		
		$sql = "DELETE FROM	wcf".WCF_N."_clipboard_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
	
	/**
	 * Unmarks all items of given type.
	 * 
	 * @param	integer		$objectTypeID
	 */
	public function unmarkAll($objectTypeID) {
		$sql = "DELETE FROM	wcf".WCF_N."_clipboard_item
			WHERE		objectTypeID = ?
					AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeID,
			WCF::getUser()->userID
		));
	}
	
	/**
	 * Returns a type id by name.
	 * 
	 * @param	string		$typeName
	 * @return	integer
	 */
	public function getObjectTypeID($typeName) {
		if (isset($this->cache['objectTypeNames'][$typeName])) {
			return $this->cache['objectTypeNames'][$typeName];
		}
		
		return null;
	}
	
	/**
	 * Returns a type by object type id.
	 * 
	 * @param	integer				$objectTypeID
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->cache['objectTypes'][$objectTypeID])) {
			return $this->cache['objectTypes'][$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns object type by object type name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeByName($objectType) {
		foreach ($this->cache['objectTypes'] as $objectTypeID => $objectTypeObj) {
			if ($objectTypeObj->objectType == $objectType) {
				return $objectTypeID;
			}
		}
		
		return null;
	}
	
	/**
	 * Loads a list of marked items grouped by type name.
	 * 
	 * @param	integer		$objectTypeID
	 */
	protected function loadMarkedItems($objectTypeID = null) {
		if ($this->markedItems === null) {
			$this->markedItems = array();
		}
		
		if ($objectTypeID !== null) {
			$objectType = $this->getObjectType($objectTypeID);
			if ($objectType === null) {
				throw new SystemException("object type id ".$objectTypeID." is invalid");
			}
			
			if (!isset($this->markedItems[$objectType->objectType])) {
				$this->markedItems[$objectType->objectType] = array();
			}
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", array(WCF::getUser()->userID));
		if ($objectTypeID !== null) {
			$conditions->add("objectTypeID = ?", array($objectTypeID));
		}
		
		// fetch object ids
		$sql = "SELECT	objectTypeID, objectID
			FROM	wcf".WCF_N."_clipboard_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// group object ids by type name
		$data = array();
		while ($row = $statement->fetchArray()) {
			$objectType = $this->getObjectType($row['objectTypeID']);
			if ($objectType === null) {
				continue;
			}
			
			if (!isset($data[$objectType->objectType])) {
				if ($objectType->listclassname == '') {
					throw new SystemException("Missing list class for object type '".$objectType->objectType."'");
				}
				
				$data[$objectType->objectType] = array(
					'className' => $objectType->listclassname,
					'objectIDs' => array()
				);
			}
			
			$data[$objectType->objectType]['objectIDs'][] = $row['objectID'];
		}
		
		// read objects
		foreach ($data as $objectType => $objectData) {
			$objectList = new $objectData['className']();
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias() . "." . $objectList->getDatabaseTableIndexName() . " IN (?)", array($objectData['objectIDs']));
			$objectList->readObjects();
			
			$this->markedItems[$objectType] = $objectList->getObjects();
			
			// validate object ids against loaded items (check for zombie object ids)
			$indexName = $objectList->getDatabaseTableIndexName();
			foreach ($this->markedItems[$objectType] as $object) {
				$index = array_search($object->$indexName, $objectData['objectIDs']);
				unset($objectData['objectIDs'][$index]);
			}
			
			if (!empty($objectData['objectIDs'])) {
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("objectTypeID = ?", array($this->getObjectTypeByName($objectType)));
				$conditions->add("userID = ?", array(WCF::getUser()->userID));
				$conditions->add("objectID IN (?)", array($objectData['objectIDs']));
				
				$sql = "DELETE FROM	wcf".WCF_N."_clipboard_item
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
			}
		}
	}
	
	/**
	 * Loads a list of marked items grouped by type name.
	 * 
	 * @param	integer		$objectTypeID
	 */
	public function getMarkedItems($objectTypeID = null) {
		if ($this->markedItems === null) {
			$this->loadMarkedItems($objectTypeID);
		}
		
		if ($objectTypeID !== null) {
			$objectType = $this->getObjectType($objectTypeID);
			if (!isset($this->markedItems[$objectType->objectType])) {
				$this->loadMarkedItems($objectTypeID);
			}
			
			return $this->markedItems[$objectType->objectType];
		}
		
		return $this->markedItems;
	}
	
	/**
	 * Returns items for clipboard editor.
	 * 
	 * @param	string		$page
	 * @param	integer		$pageObjectID
	 * @param	array		$containerData
	 * @return	array<array>
	 */
	public function getEditorItems($page, $pageObjectID, $containerData) {
		$this->pageObjectID = 0;
		
		// ignore unknown pages
		if (!isset($this->pageCache[$page])) return null;
		
		// get objects
		$this->loadMarkedItems();
		if (empty($this->markedItems)) return null;
		
		$this->pageObjectID = $pageObjectID;
		
		// fetch action ids
		$this->loadActionCache();
		$actionIDs = array();
		foreach ($this->pageCache[$page] as $actionID) {
			if (isset($this->actionCache[$actionID])) {
				$actionIDs[] = $actionID;
			}
		}
		$actionIDs = array_unique($actionIDs);
		
		// load actions
		$actions = array();
		foreach ($actionIDs as $actionID) {
			$actionObject = $this->actionCache[$actionID];
			$actionClassName = $actionObject->actionClassName;
			if (!isset($actions[$actionClassName])) {
				// validate class
				if (!ClassUtil::isInstanceOf($actionClassName, 'wcf\system\clipboard\action\IClipboardAction')) {
					throw new SystemException("'".$actionClassName."' does not implement 'wcf\system\clipboard\action\IClipboardAction'");
				}
				
				$actions[$actionClassName] = array(
					'actions' => array(),
					'object' => new $actionClassName()
				);
			}
			
			$actions[$actionClassName]['actions'][] = $actionObject;
		}
		
		// execute actions
		$editorData = array();
		foreach ($actions as $actionData) {
			// get accepted objects
			$typeName = $actionData['object']->getTypeName();
			if (!isset($this->markedItems[$typeName]) || empty($this->markedItems[$typeName])) continue;
			
			$typeData = array();
			if (isset($containerData[$typeName])) {
				$typeData = $containerData[$typeName];
			}
			
			// filter objects by type data
			$objects = $actionData['object']->filterObjects($this->markedItems[$typeName], $typeData);
			if (empty($objects)) continue;
			
			if (!isset($editorData[$typeName])) {
				$editorData[$typeName] = array(
					'label' => $actionData['object']->getEditorLabel($objects),
					'items' => array()
				);
			}
			
			foreach ($actionData['actions'] as $actionObject) {
				$data = $actionData['object']->execute($objects, $actionObject);
				if ($data === null) {
					continue;
				}
				
				$editorData[$typeName]['items'][$actionObject->showOrder] = $data;
			}
		}
		
		return $editorData;
	}
	
	/**
	 * Removes items from clipboard.
	 * 
	 * @param	integer		$typeID
	 */
	public function removeItems($typeID = null) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", array(WCF::getUser()->userID));
		if ($typeID !== null) $conditions->add("objectTypeID = ?", array($typeID));
		
		$sql = "DELETE FROM	wcf".WCF_N."_clipboard_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
	
	/**
	 * Returns true (1) if at least one item (of the given object type) is marked.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	integer
	 */
	public function hasMarkedItems($objectTypeID = null) {
		if (!WCF::getUser()->userID) return 0;
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("userID = ?", array(WCF::getUser()->userID));
		if ($objectTypeID !== null) {
			$conditionBuilder->add("objectTypeID = ?", array($objectTypeID));
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_clipboard_item
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$count = $statement->fetchArray();
		
		if ($count['count']) {
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns page object id.
	 * 
	 * @return	integer
	 */
	public function getPageObjectID() {
		return $this->pageObjectID;
	}
}
