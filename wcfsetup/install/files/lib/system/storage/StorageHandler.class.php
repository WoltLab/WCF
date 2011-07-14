<?php
namespace wcf\system\storage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the persistent data storage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.storage
 * @category 	Community Framework
 */
class StorageHandler extends SingletonFactory {
	/**
	 * Data cache
	 * 
	 * @var	array<array>
	 */
	protected $cache = array();
	
	/**
	 * List of outdated data records
	 * 
	 * @var	array<array>
	 */
	protected $resetFields = array();
	
	/**
	 * List of updated or new data records
	 * 
	 * @var	array<array>
	 */
	protected $updateFields = array();
	
	/**
	 * Loads storage for a given set of users.
	 * 
	 * @param	array		$userIDs
	 * @param	integer		$packageID
	 */
	public function loadStorage(array $userIDs, $packageID = PACKAGE_ID) {
		$tmp = array();
		foreach ($userIDs as $userID) {
			if (!isset($this->cache[$userID])) $tmp[] = $userID;
		}
		
		// ignore users whose storage data is already loaded
		if (empty($tmp)) return;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($tmp));
		$conditions->add("packageID = ?", array(PACKAGE_ID));
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_storage
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($this->cache[$row['userID']])) {
				$this->cache[$row['userID']] = array();
			}
			
			if (!isset($this->cache[$row['userID']][$row['packageID']])) {
				$this->cache[$row['userID']][$row['packageID']] = array();
			}
			
			$this->cache[$row['userID']][$row['packageID']][$row['field']] = $row['fieldValue'];
		}
	}
	
	/**
	 * Returns stored data for given users.
	 * 
	 * @param	array		$userIDs
	 * @param	string		$field
	 * @param	integer		$packageID
	 * @return	array<array>
	 */
	public function getStorage(array $userIDs, $field, $packageID = PACKAGE_ID) {
		$data = array();
		
		foreach ($userIDs as $userID) {
			if (isset($this->cache[$userID][$packageID][$field])) {
				$data[$userID] = $this->cache[$userID][$packageID][$field];
			}
			else {
				$data[$userID] = null;
			}
		}
		
		return $data;
	}
	
	/**
	 * Inserts new data records into database.
	 * 
	 * @param	integer		$userID
	 * @param	string		$field
	 * @param	string		$fieldValue
	 * @param	integer		$packageID
	 */
	public function update($userID, $field, $fieldValue, $packageID = PACKAGE_ID) {
		$this->updateFields[$userID][$packageID][$field] = $fieldValue;
		
		// update data cache for given user
		if (!isset($this->cache[$userID])) {
			$this->cache[$userID] = array();
		}
		
		if (!isset($this->cache[$userID][$packageID])) {
			$this->cache[$userID][$packageID] = array();
		}
		
		$this->cache[$userID][$packageID][$field] = $fieldValue;
		
		// flag key as outdated
		self::reset($userID, $field, $packageID);
	}
	
	/**
	 * Removes a data record from database.
	 * 
	 * @param	integer		$userID
	 * @param	string		$field
	 * @param	integer		$packageID
	 */
	public function reset($userID, $field, $packageID = PACKAGE_ID) {
		$this->resetFields[$userID][$packageID][] = $field;
	}
	
	/**
	 * Removes a specific data record for all users.
	 * 
	 * @param	string		$field
	 * @param	integer		$packageID
	 */	
	public function resetAll($field, $packageID = PACKAGE_ID) {
		$sql = "DELETE FROM	wcf".WCF_N."_storage
			WHERE		field = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$field,
			$packageID
		));
	}
	
	/**
	 * Removes and inserts data records on shutdown.
	 */
	public function shutdown() {
		// remove outdated entries
		if (count($this->resetFields)) {
			$sql = "DELETE FROM	wcf".WCF_N."_storage
				WHERE		userID = ?
						AND field = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->resetFields as $userID => $data) {
				foreach ($data as $packageID => $fields) {
					foreach ($fields as $field) {
						$statement->execute(array(
							$userID,
							$field,
							$packageID
						));
					}
				}
			}
		}
		
		// insert data
		if (count($this->updateFields)) {
			$sql = "INSERT INTO	wcf".WCF_N."_storage
						(userID, field, fieldValue, packageID)
				VALUES		(?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->updateFields as $userID => $data) {
				foreach ($data as $packageID => $fieldValues) {
					foreach ($fieldValues as $field => $fieldValue) {
						$statement->execute(array(
							$userID,
							$field,
							$fieldValue,
							$packageID
						));
					}
				}
			}
		}
	}
}
