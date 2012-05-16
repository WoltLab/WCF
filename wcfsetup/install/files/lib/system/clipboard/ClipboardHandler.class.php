<?php
namespace wcf\system\clipboard;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Handles clipboard-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard
 * @category 	Community Framework
 */
class ClipboardHandler extends SingletonFactory {
	/**
	 * cached list of actions
	 * @var	array
	 */
	protected $actionCache = null;
	
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
	 * cached list of clipboard item types
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
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
		
		CacheHandler::getInstance()->addResource(
			'clipboard-page-'.PACKAGE_ID,
			WCF_DIR.'cache/cache.clipboard-page-'.PACKAGE_ID.'.php',
			'wcf\system\cache\builder\ClipboardPageCacheBuilder'
		);
		$this->pageCache = CacheHandler::getInstance()->get('clipboard-page-'.PACKAGE_ID);
	}
	
	/**
	 * Loads action cache.
	 */
	protected function loadActionCache() {
		if ($this->actionCache !== null) return;
		
		CacheHandler::getInstance()->addResource(
			'clipboard-action-'.PACKAGE_ID,
			WCF_DIR.'cache/cache.clipboard-action-'.PACKAGE_ID.'.php',
			'wcf\system\cache\builder\ClipboardActionCacheBuilder'
		);
		$this->actionCache = CacheHandler::getInstance()->get('clipboard-action-'.PACKAGE_ID);
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
	 * Returns a type by type id.
	 * 
	 * @param	integer		$typeID
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($typeID) {
		if (isset($this->cache['objectTypes'][$typeID])) {
			return $this->cache['objectTypes'][$typeID];
		}
		
		return null;
	}
	
	/**
	 * Loads a list of marked items grouped by type name.
	 * 
	 * @param	integer		$objectTypeID
	 */
	protected function loadMarkedItems($objectTypeID = null) {
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
		$this->markedItems = array();
		foreach ($data as $objectType => $objectData) {
			$objectList = new $objectData['className']();
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias() . "." . $objectList->getDatabaseTableIndexName() . " IN (?)", array($objectData['objectIDs']));
			$objectList->sqlLimit = 0;
			$objectList->readObjects();
			
			$this->markedItems[$objectType] = $objectList->getObjects();
		}
	}
	
	/**
	 * Loads a list of marked items grouped by type name.
	 * 
	 * @param	integer		$typeID
	 */
	public function getMarkedItems($typeID = null) {
		if ($this->markedItems === null) {
			$this->loadMarkedItems($typeID);
		}
		
		return $this->markedItems;
	}
	
	/**
	 * Returns items for clipboard editor.
	 * 
	 * @param	string		$page
	 * @param	array		$containerData
	 * @return	array<array>
	 */
	public function getEditorItems($page, $containerData) {
		// ignore unknown pages
		if (!isset($this->pageCache[$page])) return null;
		
		// get objects
		$this->loadMarkedItems();
		if (!count($this->markedItems)) return null;
		
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
			$actionClassName = $this->actionCache[$actionID]->actionClassName;
			$actionName = $this->actionCache[$actionID]->actionName;
			if (!isset($actions[$actionClassName])) {
				// validate class
				if (!ClassUtil::isInstanceOf($actionClassName, 'wcf\system\clipboard\action\IClipboardAction')) {
					throw new SystemException("class '".$actionClassName."' does not implement the interface 'wcf\system\clipboard\action\IClipboardAction'.");
				}
				
				$actions[$actionClassName] = array(
					'actions' => array(),
					'object' => new $actionClassName()
				);
			}
			
			$actions[$actionClassName]['actions'][] = $actionName;
		}
		
		// execute actions
		$editorData = array();
		foreach ($actions as $actionData) {
			// get accepted objects
			$typeName = $actionData['object']->getTypeName();
			if (!isset($this->markedItems[$typeName])) continue;
			
			$editorData[$typeName] = array(
				'label' => $actionData['object']->getEditorLabel($this->markedItems[$typeName]),
				'items' => array()
			);
			
			$typeData = array();
			if (isset($containerData[$typeName])) {
				$typeData = $containerData[$typeName];
			}
			
			foreach ($actionData['actions'] as $action) {
				$data = $actionData['object']->execute($this->markedItems[$typeName], $action, $typeData);
				if ($data === null) {
					continue;
				}
				
				$editorData[$typeName]['items'][$action] = $data;
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
		if ($typeID !== null) $conditions->add("typeID = ?", array($typeID));
		
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
}
