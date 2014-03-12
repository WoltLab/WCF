<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * MemcachedCacheSource is an implementation of CacheSource that uses a Memcached server to store cached variables.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
class MemcachedCacheSource implements ICacheSource {
	/**
	 * memcached object
	 * @var	\Memcached
	 */
	protected $memcached = null;
	
	/**
	 * key prefix
	 * @var	string
	 */
	protected $prefix = '';
	
	/**
	 * Creates a new instance of memcached.
	 */
	public function __construct() {
		if (!class_exists('Memcached')) {
			throw new SystemException('memcached support is not enabled.');
		}
		
		// init memcached
		$this->memcached = new \Memcached();
		
		// add servers
		$tmp = explode("\n", StringUtil::unifyNewlines(CACHE_SOURCE_MEMCACHED_HOST));
		$servers = array();
		$defaultWeight = floor(100 / count($tmp));
		$regex = new Regex('^\[([a-z0-9\:\.]+)\](?::([0-9]{1,5}))?(?::([0-9]{1,3}))?$', Regex::CASE_INSENSITIVE);
		
		foreach ($tmp as $server) {
			$server = StringUtil::trim($server);
			if (!empty($server)) {
				$host = $server;
				$port = 11211; // default memcached port
				$weight = $defaultWeight;
				
				// check for IPv6
				if ($regex->match($host)) {
					$matches = $regex->getMatches();
					$host = $matches[1];
					if (isset($matches[2])) {
						$port = $matches[2];
					}
					if (isset($matches[3])) {
						$weight = $matches[3];
					}
				}
				else {
					// IPv4, try to get port and weight
					if (strpos($host, ':')) {
						$parsedHost = explode(':', $host);
						$host = $parsedHost[0];
						$port = $parsedHost[1];
						
						if (isset($parsedHost[2])) {
							$weight = $parsedHost[2];
						}
					}
				}
				
				$servers[] = array($host, $port, $weight);
			}
		}
		
		$this->memcached->addServers($servers);
		
		// test connection
		$this->memcached->get('testing');
		
		// set variable prefix to prevent collision
		$this->prefix = substr(sha1(WCF_DIR), 0, 8) . '_';
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush($cacheName, $useWildcard) {
		$cacheName = $this->prefix . $cacheName;
		
		$resources = ($useWildcard) ? $this->getResources('~^' . $cacheName. '(-[a-f0-9]+)?$~') : array($cacheName);
		foreach ($resources as $resource) {
			$this->memcached->delete($resource);
			$this->updateMaster(null, $resource);
		}
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flushAll()
	 */
	public function flushAll() {
		// read all keys
		$availableKeys = $this->memcached->get($this->prefix . 'master');
		if ($availableKeys !== false) {
			$keys = @unserialize($availableKeys);
			if ($keys !== false) {
				foreach ($keys as $key) {
					$this->memcached->delete($key);
				}
			}
		}
		
		// flush master
		$this->memcached->set($this->prefix . 'master', serialize(array()), $this->getTTL());
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::get()
	 */
	public function get($cacheName, $maxLifetime) {
		$cacheName = $this->prefix . $cacheName;
		$value = $this->memcached->get($cacheName);
		
		if ($value === false) {
			// check if value does not exist
			if ($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
				$this->updateMaster(null, $cacheName);
				return null;
			}
		}
		
		$this->updateMaster($cacheName);
		return $value;
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::set()
	 */
	public function set($cacheName, $value, $maxLifetime) {
		$cacheName = $this->prefix . $cacheName;
		$this->memcached->set($cacheName, $value, $this->getTTL($maxLifetime));
		
		$this->updateMaster($cacheName);
	}
	
	/**
	 * Updates master record for cached resources.
	 * 
	 * @param	string		$addResource
	 * @param	string		$removeResource
	 */
	protected function updateMaster($addResource = null, $removeResource = null) {
		if ($addResource === null && $removeResource === null) {
			return;
		}
		
		$master = $this->memcached->get($this->prefix . 'master');
		$update = false;
		
		// master record missing
		if ($master === false) {
			$update = true;
			$master = array();
		}
		else {
			$master = @unserialize($master);
			
			// master record is broken
			if ($master === false) {
				$update = true;
				$master = array();
			}
			else {
				foreach ($master as $index => $key) {
					if ($addResource !== null) {
						// key is already tracked
						if ($key === $addResource) {
							$addResource = null;
							
							if ($removeResource === null) {
								break;
							}
						}
					}
					
					if ($removeResource !== null) {
						if ($key === $removeResource) {
							$update = true;
							unset($master[$index]);
							
							if ($addResource === null) {
								break;
							}
							else {
								$removeResource = null;
							}
						}
					}
				}
				
				if ($addResource !== null) {
					$update = true;
					$master[] = $addResource;
				}
			}
		}
		
		// update master record
		if ($update) {
			$this->memcached->set($this->prefix . 'master', serialize($master), $this->getTTL());
		}
	}
	
	/**
	 * Returns time to live in seconds, defaults to 3 days.
	 * 
	 * @param	integer		$maxLifetime
	 * @return	integer
	 */
	protected function getTTL($maxLifetime = 0) {
		// max lifetime is a timestamp -> http://www.php.net/manual/en/memcached.expiration.php
		if ($maxLifetime && ($maxLifetime <= (60 * 60 * 24 * 30) || $maxLifetime >= TIME_NOW)) {
			return $maxLifetime;
		}
		
		// default TTL: 3 days
		return (60 * 60 * 24 * 3);
	}
	
	/**
	 * Gets a list of resources matching given pattern.
	 * 
	 * @param	string		$pattern
	 * @return	array<string>
	 */
	protected function getResources($pattern) {
		$resources = array();
		$master = $this->memcached->get($this->prefix . 'master');
		
		if ($master !== false) {
			$master = @unserialize($master);
			
			if ($master !== false) {
				foreach ($master as $index => $key) {
					if (preg_match($pattern, $key)) {
						$resources[] = $key;
					}
				}
			}
		}
		
		return $resources;
	}
}
