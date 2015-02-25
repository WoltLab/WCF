<?php
namespace wcf\system\user\storage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the persistent user data storage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.storage
 * @category	Community Framework
 */
class UserStorageHandler extends SingletonFactory {
	/**
	 * data cache
	 * @var	array<array>
	 */
	protected $cache = array();
	
	/**
	 * list of outdated data records
	 * @var	array<array>
	 */
	protected $resetFields = array();
	
	/**
	 * list of updated or new data records
	 * @var	array<array>
	 */
	protected $updateFields = array();
	
	/**
	 * Loads storage for a given set of users.
	 * 
	 * @param	array<integer>	$userIDs
	 */
	public function loadStorage(array $userIDs) {
		$tmp = array();
		foreach ($userIDs as $userID) {
			if (!isset($this->cache[$userID])) $tmp[] = $userID;
		}
		
		// ignore users whose storage data is already loaded
		if (empty($tmp)) return;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($tmp));
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_storage
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($this->cache[$row['userID']])) {
				$this->cache[$row['userID']] = array();
			}
			
			$this->cache[$row['userID']][$row['field']] = $row['fieldValue'];
		}
	}
	
	/**
	 * Returns stored data for given users.
	 * 
	 * @param	array<integer>	$userIDs
	 * @param	string		$field
	 * @return	array<array>
	 */
	public function getStorage(array $userIDs, $field) {
		$data = array();
		
		foreach ($userIDs as $userID) {
			if (isset($this->cache[$userID][$field])) {
				$data[$userID] = $this->cache[$userID][$field];
			}
			else {
				$data[$userID] = null;
			}
		}
		
		return $data;
	}
	
	/**
	 * Returns the value of the given field for a certain user or null if no
	 * such value exists. If no userID is given, the id of the current user
	 * is used.
	 * 
	 * In contrast to getStorage(), this method calls loadStorage() if no stored
	 * data for the user has been loaded yet!
	 * 
	 * @param	string		$field
	 * @param	integer		$userID
	 * @return	mixed
	 */
	public function getField($field, $userID = null) {
		if ($userID === null) {
			$userID = WCF::getUser()->userID;
		}
		
		if (!$userID) {
			return null;
		}
		
		// make sure stored data is loaded
		if (!isset($this->cache[$userID])) {
			$this->loadStorage(array($userID));
		}
		
		if (isset($this->cache[$userID][$field])) {
			return $this->cache[$userID][$field];
		}
		
		return null;
	}
	
	/**
	 * Inserts new data records into database.
	 * 
	 * @param	integer		$userID
	 * @param	string		$field
	 * @param	string		$fieldValue
	 */
	public function update($userID, $field, $fieldValue) {
		$this->updateFields[$userID][$field] = $fieldValue;
		
		// update data cache for given user
		if (isset($this->cache[$userID])) {
			$this->cache[$userID][$field] = $fieldValue;
		}
	}
	
	/**
	 * Removes a data record from database.
	 * 
	 * @param	array<integer>	$userIDs
	 * @param	string		$field
	 */
	public function reset(array $userIDs, $field) {
		foreach ($userIDs as $userID) {
			$this->resetFields[$userID][] = $field;
			
			if (isset($this->cache[$userID][$field])) {
				unset($this->cache[$userID][$field]);
			}
		}
	}
	
	/**
	 * Removes a specific data record for all users.
	 * 
	 * @param	string		$field
	 */
	public function resetAll($field) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_storage
			WHERE		field = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($field));
		
		foreach ($this->cache as $userID => $fields) {
			if (isset($fields[$field])) {
				unset($this->cache[$userID][$field]);
			}
		}
	}
	
	/**
	 * Removes and inserts data records on shutdown.
	 */
	public function shutdown() {
		WCF::getDB()->beginTransaction();
		
		// remove outdated entries
		if (!empty($this->resetFields)) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_storage
				WHERE		userID = ?
						AND field = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->resetFields as $userID => $fields) {
				foreach ($fields as $field) {
					$statement->execute(array(
						$userID,
						$field
					));
				}
			}
		}
		
		// insert data
		if (!empty($this->updateFields)) {
			// exclude values which should be resetted
			foreach ($this->updateFields as $userID => $fieldValues) {
				if (isset($this->resetFields[$userID])) {
					foreach ($fieldValues as $field => $fieldValue) {
						if (in_array($field, $this->resetFields[$userID])) {
							unset($this->updateFields[$userID][$field]);
						}
					}
					
					if (empty($this->updateFields[$userID])) {
						unset($this->updateFields[$userID]);
					}
				}
			}
			
			if (!empty($this->updateFields)) {
				$sql = "REPLACE INTO	wcf".WCF_N."_user_storage
							(userID, field, fieldValue)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				foreach ($this->updateFields as $userID => $fieldValues) {
					foreach ($fieldValues as $field => $fieldValue) {
						$statement->execute(array(
							$userID,
							$field,
							$fieldValue
						));
					}
				}
			}
		}
		
		WCF::getDB()->commitTransaction();
		
		$this->resetFields = $this->updateFields = array();
	}
	
	/**
	 * Removes the entire user storage data.
	 */
	public function clear() {
		$this->resetFields = $this->updateFields = array();
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_storage";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
}
