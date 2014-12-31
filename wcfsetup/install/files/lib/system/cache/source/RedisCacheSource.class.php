<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * RedisCacheSource is an implementation of CacheSource that uses a Redis server to store cached variables.
 * 
 * @author	Maximilian Mader
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
class RedisCacheSource implements ICacheSource {
	/**
	 * Redis object
	 * @var	\Redis
	 */
	protected $redis = null;
	
	/**
	 * Creates a new instance of Redis.
	 */
	public function __construct() {
		if (!class_exists('Redis')) {
			throw new SystemException('Redis support is not enabled.');
		}
		
		$this->redis = new \Redis();
		
		$regex = new Regex('^\[([a-z0-9\:\.]+)\](?::([0-9]{1,5}))?$', Regex::CASE_INSENSITIVE);
		$host = StringUtil::trim(CACHE_SOURCE_REDIS_HOST);
		$port = 6379; // default Redis port
		
		// check for IPv6
		if ($regex->match($host)) {
			$matches = $regex->getMatches();
			$host = $matches[1];
			
			if (isset($matches[2])) {
				$port = $matches[2];
			}
		}
		else {
			// IPv4 or host, try to get port
			if (strpos($host, ':')) {
				$parsedHost = explode(':', $host);
				$host = $parsedHost[0];
				$port = $parsedHost[1];
			}
		}
		
		if (!$this->redis->connect($host, $port)) {
			throw new SystemException('Unable to connect to Redis server');
		}
		
		// automatically prefix key names with the WCF UUID
		$this->redis->setOption(\Redis::OPT_PREFIX, WCF_UUID.':');
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush($cacheName, $useWildcard) {
		
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flushAll()
	 */
	public function flushAll() {
		
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::get()
	 */
	public function get($cacheName, $maxLifetime) {
		
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
	 * @see	\wcf\system\cache\source\ICacheSource::set()
	 */
	public function set($cacheName, $value, $maxLifetime) {
		// split parameterized cache entry names into cache name and cache index
		$parts = explode('-', $cacheName, 2);
		
		// check if entry is parameterized
		if (isset($parts[1])) {
			// save parameterized cache entries as field in a hashset
			$this->redis->hset($parts[0], $parts[1], serialize($value));
		}
		else {
			// save normal cache entries as simple key
			$this->redis->setex($cacheName, $this->getTTL($maxLifetime), serialize($value));
		}
	}
	
	/**
	 * Returns the name for the given cache name in respect to flush count.
	 * 
	 * @param	string		$cacheName
	 * @return	string
	 */
	protected function getCacheName($cacheName) {
		$flush = $this->redis->get('_flush');
		
		// create flush counter if it does not exist
		if ($flush === false) {
			$this->redis->setnx('_flush', TIME_NOW);
			$this->redis->incr('_flush');
			
			$flush = $this->redis->get('_flush');
		}
		
		return $flush.':'.$cacheName;
	}
}
