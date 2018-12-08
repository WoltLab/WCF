<?php
namespace wcf\system\cache\source;
use wcf\system\database\Redis;
use wcf\system\exception\SystemException;

/**
 * RedisCacheSource is an implementation of CacheSource that uses a Redis server to store cached variables.
 * 
 * @author	Maximilian Mader
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Source
 * @since	3.0
 */
class RedisCacheSource implements ICacheSource {
	/**
	 * Redis object
	 * @var	Redis
	 */
	protected $redis = null;
	
	/**
	 * Creates a new instance of Redis.
	 */
	public function __construct() {
		try {
			$this->redis = new Redis(CACHE_SOURCE_REDIS_HOST);
		}
		catch (\Exception $e) {
			throw new SystemException('Unable to create a Redis instance', 0, '', $e);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function flush($cacheName, $useWildcard) {
		$parts = explode('-', $cacheName, 2);
		
		// check if the key is saved in a hashset
		if (isset($parts[1])) {
			if ($useWildcard) {
				// delete the complete hashset
				$this->redis->del($this->getCacheName($parts[0]));
			}
			else {
				// delete the specified key from the hashset
				$this->redis->hDel($this->getCacheName($parts[0]), $parts[1]);
			}
		}
		else {
			$this->redis->del($this->getCacheName($cacheName));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function flushAll() {
		// set flush key to current time if it does not exist yet (this prevents falling back to 0 if the key gets deleted)
		$this->redis->setnx('cache:_flush', TIME_NOW);
		
		// atomic increment of flush count
		$this->redis->incr('cache:_flush');
	}
	
	/**
	 * @inheritDoc
	 */
	public function get($cacheName, $maxLifetime) {
		$parts = explode('-', $cacheName, 2);
		
		if (isset($parts[1])) {
			$value = $this->redis->hGet($this->getCacheName($parts[0]), $parts[1]);
		}
		else {
			$value = $this->redis->get($this->getCacheName($cacheName));
		}
		
		// check if the key exist
		if ($value === false) {
			return null;
		}
		
		$value = @unserialize($value);
		
		// check if value is valid
		if ($value === false) {
			return null;
		}
		
		return $value;
	}
	
	/**
	 * Returns time to live in seconds, defaults to 3 days.
	 * 
	 * @param	integer		$maxLifetime
	 * @return	integer
	 */
	protected function getTTL($maxLifetime = 0) {
		if ($maxLifetime) return $maxLifetime;
		
		// default to 3 days
		return 60 * 60 * 24 * 3;
	}
	
	/**
	 * @inheritDoc
	 */
	public function set($cacheName, $value, $maxLifetime) {
		// split parameterized cache entry names into cache name and cache index
		$parts = explode('-', $cacheName, 2);
		
		// check if entry is parameterized
		if (isset($parts[1])) {
			$key = $this->getCacheName($parts[0]);
			
			// save parameterized cache entries as field in a hashset
			// saving in a hashset is safe as the smallest lifetime of its fields is set as TTL for the whole hashset
			$this->redis->hSet($key, $parts[1], serialize($value));
			
			$keyTTL = $this->redis->ttl($key);
			$newTTL = $this->getTTL($maxLifetime);
			
			// set a new TTL if no TTL is set or if the current TTL is longer than the new one.
			if ($keyTTL < 0 || $keyTTL > $newTTL) {
				$this->redis->expire($key, $newTTL);
			}
		}
		else {
			// save normal cache entries as simple key
			$this->redis->setex($this->getCacheName($cacheName), $this->getTTL($maxLifetime), serialize($value));
		}
	}
	
	/**
	 * Returns the name for the given cache name in respect to flush count.
	 * 
	 * @param	string		$cacheName
	 * @return	string
	 */
	protected function getCacheName($cacheName) {
		$flush = $this->redis->get('cache:_flush');
		
		// create flush counter if it does not exist
		if ($flush === false) {
			$this->redis->setnx('cache:_flush', TIME_NOW);
			$this->redis->incr('cache:_flush');
			
			$flush = $this->redis->get('cache:_flush');
		}
		
		return 'cache:'.$flush.':'.$cacheName;
	}
	
	/**
	 * Returns the Redis server version
	 * 
	 * @return	string
	 */
	public function getRedisVersion() {
		$info = $this->redis->info('server');
		
		return $info['redis_version'];
	}

	/**
	 * Returns the underlying Redis instance.
	 *
	 * @return	Redis
	 */
	public function getRedis() {
		return $this->redis;
	}
}
