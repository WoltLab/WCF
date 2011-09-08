<?php
namespace wcf\system\clipboard;
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
	 * cached list of page actions
	 * @var	array
	 */
	protected $pageCache = null;
	
	/**
	 * cached list of clipboard item types
	 * @var	array<array>
	 */
	protected $typeCache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		CacheHandler::getInstance()->addResource(
			'clipboard-itemType-'.PACKAGE_ID,
			WCF_DIR.'cache/cache.clipboard-itemType-'.PACKAGE_ID.'.php',
			'wcf\system\cache\builder\ClipboardItemTypeCacheBuilder'
		);
		$this->typeCache = CacheHandler::getInstance()->get('clipboard-itemType-'.PACKAGE_ID);
		
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
	 * @param	integer		$typeID
	 */
	public function mark(array $objectIDs, $typeID) {
		// remove existing entries first, prevents conflict with INSERT
		$this->unmark($objectIDs, $typeID);
		
		$sql = "INSERT INTO	wcf".WCF_N."_clipboard_item
					(typeID, userID, objectID)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($objectIDs as $objectID) {
			$statement->execute(array(
				$typeID,
				WCF::getUser()->userID,
				$objectID
			));
		}
	}
	
	/**
	 * Removes an object marking.
	 * 
	 * @param	array		$objectIDs
	 * @param	integer		$typeID
	 */
	public function unmark(array $objectIDs, $typeID) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("typeID = ?", array($typeID));
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
	public function getTypeID($typeName) {
		if (isset($this->typeCache['typeNames'][$typeName])) {
			return $this->typeCache['typeNames'][$typeName];
		}
		
		return null;
	}
	
	/**
	 * Returns a type by type id.
	 * 
	 * @param	integer		$typeID
	 * @return	wcf\data\clipboard\item\type\ClipboardItemType
	 */
	public function getType($typeID) {
		if (isset($this->typeCache['types'][$typeID])) {
			return $this->typeCache['types'][$typeID];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of marked items grouped by type name.
	 * 
	 * @param	integer		$typeID
	 * @return	array<array>
	 */
	public function getMarkedItems($typeID = null) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", array(WCF::getUser()->userID));
		if ($typeID !== null) $conditions->add("typeID = ?", array($typeID));
		
		// fetch object ids
		$sql = "SELECT	typeID, objectID
			FROM	wcf".WCF_N."_clipboard_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// group object ids by type name
		$data = array();
		while ($row = $statement->fetchArray()) {
			$type = $this->getType($row['typeID']);
			if ($type === null) {
				continue;
			}
			
			if (!isset($data[$type->typeName])) {
				$data[$type->typeName] = array(
					'className' => $type->listClassName,
					'objectIDs' => array()
				);
			}
			
			$data[$type->typeName]['objectIDs'][] = $row['objectID'];
		}
		
		// read objects
		$objects = array();
		foreach ($data as $typeName => $objectData) {
			$objectList = new $objectData['className']();
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias() . "." . $objectList->getDatabaseTableIndexName() . " IN (?)", array($objectData['objectIDs']));
			$objectList->sqlLimit = 0;
			$objectList->readObjects();
			
			$objects[$typeName] = $objectList->getObjects();
		}
		
		return $objects;
	}
	
	/**
	 * Returns items for clipboard editor.
	 * 
	 * @param	string		$page
	 * @return	array<array>
	 */
	public function getEditorItems($page) {
		// ignore unknown pages
		if (!isset($this->pageCache[$page])) return null;
		
		// get objects
		$objects = $this->getMarkedItems();
		if (!count($objects)) return null;
		
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
			if (!isset($objects[$typeName])) continue;
			
			$editorData[$typeName] = array(
				'label' => $actionData['object']->getEditorLabel($objects[$typeName]),
				'items' => array()
			);
			
			foreach ($actionData['actions'] as $action) {
				$data = $actionData['object']->execute($objects[$typeName], $action);
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
}
