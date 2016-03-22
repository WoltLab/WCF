<?php
namespace wcf\system\importer;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\IAJAXInvokeAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles data import.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class ImportHandler extends SingletonFactory implements IAJAXInvokeAction {
	/**
	 * id map cache
	 * @var	array
	 */
	protected $idMappingCache = array();
	
	/**
	 * list of available importers
	 * @var	array
	 */
	protected $objectTypes = array();
	
	/**
	 * list of available importer processors
	 * @var	array
	 */
	protected $importers = array();
	
	/**
	 * user merge mode
	 * @var	integer
	 */
	protected $userMergeMode = 2;
	
	/**
	 * import hash
	 * @var	string
	 */
	protected $importHash = '';
	
	/**
	 * list of methods allowed for remote invoke
	 * @var	array<string>
	 */
	public static $allowInvoke = array('resetMapping');
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer');
	}
	
	/**
	 * Gets a data importer.
	 * 
	 * @param	string		$type
	 * @return	\wcf\system\importer\IImporter
	 */
	public function getImporter($type) {
		if (!isset($this->importers[$type])) {
			if (!isset($this->objectTypes[$type])) {
				throw new SystemException("unknown importer '".$type."'");
			}
			
			$this->importers[$type] = $this->objectTypes[$type]->getProcessor();
		}
		
		return $this->importers[$type];
	}
	
	/**
	 * Returns a new id from id mapping.
	 * 
	 * @param	string		$type
	 * @param	mixed		$oldID
	 * @return	integer		$newID
	 */
	public function getNewID($type, $oldID) {
		if (!$oldID) return null;
		$objectTypeID = $this->objectTypes[$type]->objectTypeID;
		
		if (!isset($this->idMappingCache[$objectTypeID]) || !array_key_exists($oldID, $this->idMappingCache[$objectTypeID])) {
			$this->idMappingCache[$objectTypeID][$oldID] = null;
			$importer = $this->getImporter($type);
			$tableName = $indexName = '';
			if ($importer->getClassName()) {
				$tableName = call_user_func(array($importer->getClassName(), 'getDatabaseTableName'));
				$indexName = call_user_func(array($importer->getClassName(), 'getDatabaseTableIndexName'));
			}
			
			$sql = "SELECT		import_mapping.newID
				FROM		wcf".WCF_N."_import_mapping import_mapping
				".($tableName ? "LEFT JOIN ".$tableName." object_table ON (object_table.".$indexName." = import_mapping.newID)" : '')."
				WHERE		import_mapping.importHash = ?
						AND import_mapping.objectTypeID = ?
						AND import_mapping.oldID = ?
						".($tableName ? "AND object_table.".$indexName." IS NOT NULL" : '');
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->importHash, $objectTypeID, $oldID));
			$row = $statement->fetchArray();
			if ($row !== false) $this->idMappingCache[$objectTypeID][$oldID] = $row['newID'];
		}
		
		return $this->idMappingCache[$objectTypeID][$oldID];
	}
	
	/**
	 * Saves an id mapping.
	 * 
	 * @param	string		$type
	 * @param	integer		$oldID
	 * @param	integer		$newID
	 */
	public function saveNewID($type, $oldID, $newID) {
		$objectTypeID = $this->objectTypes[$type]->objectTypeID;
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_import_mapping
						(importHash, objectTypeID, oldID, newID)
			VALUES			(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->importHash, $objectTypeID, $oldID, $newID));
		
		unset($this->idMappingCache[$objectTypeID][$oldID]);
	}
	
	/**
	 * Validates accessibility of resetMapping().
	 */
	public function validateResetMapping() {
		WCF::getSession()->checkPermissions(array('admin.management.canImportData'));
		
		// reset caches
		CacheHandler::getInstance()->flushAll();
		UserStorageHandler::getInstance()->clear();
	}
	
	/**
	 * Resets the mapping.
	 */
	public function resetMapping() {
		$sql = "DELETE FROM	wcf".WCF_N."_import_mapping";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$this->idMappingCache = array();
	}
	
	/**
	 * Sets the user merge mode.
	 * 
	 * @param	integer		$mode
	 */
	public function setUserMergeMode($mode) {
		$this->userMergeMode = $mode;
	}
	
	/**
	 * Returns the user merge mode.
	 * 
	 * @return	integer
	 */
	public function getUserMergeMode() {
		return $this->userMergeMode;
	}
	
	/**
	 * Sets the import hash.
	 * 
	 * @param	string		$hash
	 */
	public function setImportHash($hash) {
		$this->importHash = $hash;
	}
}
