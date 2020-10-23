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
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
	 * @var (string|null)[][]
	 */
	protected $log = [];
	
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
		$this->validateUserIDs($userIDs);
		
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
			
			if (isset($this->log[$row['userID']])) {
				if (array_key_exists($row['field'], $this->log[$row['userID']])) {
					$logged = $this->log[$row['userID']][$row['field']];
					
					// Use the new value if the field was updated.
					if ($logged !== null) {
						$this->cache[$row['userID']][$row['field']] = $logged;
					}
					
					// In any case: Skip this field, because it was updated or resetted before it was loaded.
					continue;
				}
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
		$this->validateUserIDs($userIDs);
		
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
		$this->validateUserIDs([$userID]);
		
		if ($this->redis) {
			$this->redis->hSet($this->getRedisFieldName($field), $userID, $fieldValue);
			$this->redis->expire($this->getRedisFieldName($field), 86400);
			return;
		}
		
		if (!isset($this->log[$userID])) {
			$this->log[$userID] = [];
		}
		$this->log[$userID][$field] = $fieldValue;
		
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
		$this->validateUserIDs($userIDs);
		
		if ($this->redis) {
			foreach ($userIDs as $userID) {
				$this->redis->hDel($this->getRedisFieldName($field), $userID);
			}
			return;
		}
		
		foreach ($userIDs as $userID) {
			if (!isset($this->log[$userID])) {
				$this->log[$userID] = [];
			}
			$this->log[$userID][$field] = null;
			
			unset($this->cache[$userID][$field]);
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
			unset($this->cache[$userID][$field]);
		}
		
		foreach ($this->log as $userID => $fields) {
			unset($this->log[$userID][$field]);
		}
	}
	
	/**
	 * Removes and inserts data records on shutdown.
	 */
	public function shutdown() {
		if ($this->redis) return;
		
		$i = 0;
		while (true) {
			try {
				foreach ($this->log as $userID => $fields) {
					WCF::getDB()->beginTransaction();
					
					ksort($fields);
					
					// Lock the user.
					$sql = "SELECT	*
						FROM	wcf".WCF_N."_user
						WHERE	userID = ?
						FOR UPDATE";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$userID]);
					
					// Delete existing data.
					$conditions = new PreparedStatementConditionBuilder();
					$conditions->add("userID = ?", [$userID]);
					$conditions->add("field IN (?)", [array_keys($fields)]);
					
					$sql = "DELETE FROM	wcf".WCF_N."_user_storage
						".$conditions;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditions->getParameters());
					
					// Insert updated data.
					$sql = "INSERT INTO	wcf".WCF_N."_user_storage
								(userID, field, fieldValue)
						VALUES		(?, ?, ?)";
					$statement = WCF::getDB()->prepareStatement($sql);
					foreach ($fields as $field => $fieldValue) {
						if ($fieldValue === null) continue;
						
						$statement->execute([
							$userID,
							$field,
							$fieldValue
						]);
					}
					
					WCF::getDB()->commitTransaction();
					
					// Delete log entry as the commit succeeded.
					unset($this->log[$userID]);
				}
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
		
		$this->cache = [];
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_storage";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$this->log = [];
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
	
	/**
	 * @param int[] $userIDs
	 * @since 5.2
	 */
	protected function validateUserIDs(array $userIDs) {
		foreach ($userIDs as $userID) {
			if (!$userID) {
				throw new \InvalidArgumentException('The user id can neither be null nor zero.');
			}
		}
	}
}
