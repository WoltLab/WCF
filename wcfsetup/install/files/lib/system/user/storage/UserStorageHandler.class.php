<?php
namespace wcf\system\user\storage;
use wcf\system\cache\source\RedisCacheSource;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\database\Redis;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the persistent user data storage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Storage
 */
class UserStorageHandler extends SingletonFactory {
	/**
	 * data cache
	 * @var	mixed[][]
	 */
	protected $cache = [];
	
	/**
	 * list of outdated data records
	 * @var	mixed[][]
	 */
	protected $resetFields = [];
	
	/**
	 * list of updated or new data records
	 * @var	mixed[][]
	 */
	protected $updateFields = [];
	
	/**
	 * redis instance
	 * @var	Redis
	 */
	protected $redis;
	
	/**
	 * Checks whether Redis is available.
	 */
	protected function init() {
		$cacheSource = CacheHandler::getInstance()->getCacheSource();
		if ($cacheSource instanceof RedisCacheSource) {
			$this->redis = $cacheSource->getRedis();
		}
	}
	
	/**
	 * Loads storage for a given set of users.
	 * 
	 * @param	integer[]	$userIDs
	 */
	public function loadStorage(array $userIDs) {
		if ($this->redis) return;
		
		$tmp = [];
		foreach ($userIDs as $userID) {
			if (!isset($this->cache[$userID])) $tmp[] = $userID;
		}
		
		// ignore users whose storage data is already loaded
		if (empty($tmp)) return;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$tmp]);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_storage
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($this->cache[$row['userID']])) {
				$this->cache[$row['userID']] = [];
			}
			
			$this->cache[$row['userID']][$row['field']] = $row['fieldValue'];
		}
	}
	
	/**
	 * Returns stored data for given users.
	 * 
	 * @param	integer[]	$userIDs
	 * @param	string		$field
	 * @return	mixed[]
	 */
	public function getStorage(array $userIDs, $field) {
		$data = [];
		
		if ($this->redis) {
			foreach ($userIDs as $userID) {
				$data[$userID] = $this->redis->hGet($this->getRedisFieldName($field), $userID);
				if ($data[$userID] === false) $data[$userID] = null;
			}
			
			return $data;
		}
		
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
		
		if ($this->redis) {
			$result = $this->redis->hGet($this->getRedisFieldName($field), $userID);
			if ($result === false) return null;
			return $result;
		}
		
		// make sure stored data is loaded
		if (!isset($this->cache[$userID])) {
			$this->loadStorage([$userID]);
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
		if ($this->redis) {
			$this->redis->hSet($this->getRedisFieldName($field), $userID, $fieldValue);
			$this->redis->expire($this->getRedisFieldName($field), 86400);
			return;
		}
		
		$this->updateFields[$userID][$field] = $fieldValue;
		
		// update data cache for given user
		if (isset($this->cache[$userID])) {
			$this->cache[$userID][$field] = $fieldValue;
		}
	}
	
	/**
	 * Removes a data record from database.
	 * 
	 * @param	integer[]	$userIDs
	 * @param	string		$field
	 */
	public function reset(array $userIDs, $field) {
		if ($this->redis) {
			foreach ($userIDs as $userID) {
				$this->redis->hDel($this->getRedisFieldName($field), $userID);
			}
			return;
		}
		
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
		if ($this->redis) {
			$this->redis->del($this->getRedisFieldName($field));
			return;
		}
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_storage
			WHERE		field = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$field]);
		
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
		if ($this->redis) return;
		
		$toReset = [];
		
		// remove outdated entries
		foreach ($this->resetFields as $userID => $fields) {
			foreach ($fields as $field) {
				if (!isset($toReset[$field])) $toReset[$field] = [];
				$toReset[$field][] = $userID;
			}
		}
		foreach ($this->updateFields as $userID => $fieldValues) {
			foreach ($fieldValues as $field => $fieldValue) {
				if (!isset($toReset[$field])) $toReset[$field] = [];
				$toReset[$field][] = $userID;
			}
		}
		ksort($toReset);
		
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
		ksort($this->updateFields);
		
		$i = 0;
		while (true) {
			try {
				WCF::getDB()->beginTransaction();
				
				// reset data
				foreach ($toReset as $field => $userIDs) {
					sort($userIDs);
					$conditions = new PreparedStatementConditionBuilder();
					$conditions->add("userID IN (?)", [$userIDs]);
					$conditions->add("field = ?", [$field]);
					
					$sql = "DELETE FROM	wcf".WCF_N."_user_storage
						".$conditions;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditions->getParameters());
				}
				
				// insert data
				if (!empty($this->updateFields)) {
					$sql = "INSERT INTO	wcf".WCF_N."_user_storage
								(userID, field, fieldValue)
						VALUES		(?, ?, ?)";
					$statement = WCF::getDB()->prepareStatement($sql);
					
					foreach ($this->updateFields as $userID => $fieldValues) {
						ksort($fieldValues);
						
						foreach ($fieldValues as $field => $fieldValue) {
							$statement->execute([
								$userID,
								$field,
								$fieldValue
							]);
						}
					}
				}
				
				WCF::getDB()->commitTransaction();
				break;
			}
			catch (\Exception $e) {
				WCF::getDB()->rollBackTransaction();
				
				// retry up to 2 times
				if (++$i === 2) {
					\wcf\functions\exception\logThrowable($e);
					break;
				}
				
				usleep(mt_rand(0, .1e6)); // 0 to .1 seconds
			}
		}
		$this->resetFields = $this->updateFields = [];
	}
	
	/**
	 * Removes the entire user storage data.
	 */
	public function clear() {
		if ($this->redis) {
			$this->redis->setnx('ush:_flush', TIME_NOW);
			$this->redis->incr('ush:_flush');
			return;
		}
		
		$this->resetFields = $this->updateFields = [];
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_storage";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * Returns the field name for use in Redis.
	 * 
	 * @param	string	$fieldName
	 * @return	string
	 */
	protected function getRedisFieldName($fieldName) {
		$flush = $this->redis->get('ush:_flush');
		
		// create flush counter if it does not exist
		if ($flush === false) {
			$this->redis->setnx('ush:_flush', TIME_NOW);
			$this->redis->incr('ush:_flush');
			
			$flush = $this->redis->get('ush:_flush');
		}
		
		return 'ush:'.$flush.':'.$fieldName;
	}
}
