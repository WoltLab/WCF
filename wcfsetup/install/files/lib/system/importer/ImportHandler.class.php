<?php
namespace wcf\system\importer;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\IAJAXInvokeAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles data import.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
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
	 * @var array
	 */
	protected $objectTypes = array();
	
	/**
	 * list of available importer processors
	 * @var array
	 */
	protected $importers = array();
	
	/**
	 * user merge mode
	 * @var integer
	 */
	protected $userMergeMode = 2;
	
	/**
	 * list of methods allowed for remote invoke
	 * @var	array<string>
	 */
	public static $allowInvoke = array('resetMapping');
	
	/**
	 * @see wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer');
	}
	
	/**
	 * Gets a data importer.
	 * 
	 * @param	string		$type
	 * @return	wcf\system\importer\IImporter
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
	 * Gets a new id from id mapping.
	 *
	 * @param	string		$type
	 * @param	mixed		$oldID
	 * @return	integer		$newID
	 */
	public function getNewID($type, $oldID) {
		$objectTypeID = $this->objectTypes[$type]->objectTypeID;
		
		if (!isset($this->idMappingCache[$objectTypeID][$oldID])) {
			$this->idMappingCache[$objectTypeID][$oldID] = null;
			
			$sql = "SELECT	newID
				FROM	wcf".WCF_N."_import_mapping
				WHERE	objectTypeID = ?
					AND oldID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($objectTypeID, $oldID));
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
						(objectTypeID, oldID, newID)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectTypeID, $oldID, $newID));
		
		unset($this->idMappingCache[$objectTypeID][$oldID]);
	}
	
	/**
	 * Validates accessibility of resetMapping().
	 */
	public function validateResetMapping() {
		WCF::getSession()->checkPermissions(array('admin.system.canImportData'));
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
	 * Gets the user merge mode.
	 * 
	 * @return integer
	 */
	public function getUserMergeMode() {
		return $this->userMergeMode;
	}
}
