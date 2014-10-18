<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * MemcachedCacheSource is an implementation of CacheSource that uses a Memcached server to store cached variables.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
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
	 * Creates a new instance of memcached.
	 */
	public function __construct() {
		if (!class_exists('Memcached')) {
			throw new SystemException('memcached support is not enabled.');
		}
		if (!defined('\Memcached::OPT_REMOVE_FAILED_SERVERS')) {
			throw new SystemException('required \Memcached::OPT_REMOVE_FAILED_SERVERS option is not available');
		}
		
		// init memcached
		$this->memcached = new \Memcached();
		
		// disable broken hosts for the remainder of the execution
		// Note: This may cause outdated entries once the affected memcached
		// server comes back online. But it is better than completely bailing out.
		// If the outage wasn't solely related to networking the cache is flushed
		// on restart of the affected memcached instance anyway.
		$this->memcached->setOption(\Memcached::OPT_REMOVE_FAILED_SERVERS, 1);
		
		// LIBKETAMA_COMPATIBLE uses consistent hashing, which causes fewer remaps
		// in case a server is added or removed.
		$this->memcached->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
		
		$this->memcached->setOption(\Memcached::OPT_PREFIX_KEY, 'wcf_'.substr(StringUtil::getHash(WCF_DIR), 0, 6).'_');
		
		if (!WCF::debugModeIsEnabled()) {
			// use the more efficient binary protocol to communicate with the memcached instance
			// this option is disabled in debug mode to allow for easier debugging
			// with tools, such as strace(1)
			$this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
		}
		
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
		
		// test connection, set will fail if no memcached instances are available
		// if only the target for the 'connection_testing' key is unavailable the
		// requests will automatically be mapped to another server
		if (!$this->memcached->set('connection_testing', true)) {
			throw new SystemException('Unable to obtain any valid connection');
		}
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush($cacheName, $useWildcard) {
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
		$availableKeys = $this->memcached->get('master');
		if (is_array($availableKeys)) {
			foreach ($availableKeys as $key => $dummy) {
				$this->memcached->delete($key);
			}
		}
		
		// flush master
		$this->memcached->set('master', array());
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::get()
	 */
	public function get($cacheName, $maxLifetime) {
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
		
		$i = 0;
		while (true) {
			try {
				$master = $this->memcached->get('master', null, $cas);
				$update = false;
				
				// master record missing or broken
				if (!is_array($master)) {
					$update = true;
					$master = array();
				}
				else {
					if ($removeResource !== null && isset($master[$removeResource])) {
						unset($master[$removeResource]);
						$update = true;
					}
					
					if ($addResource !== null && !isset($master[$addResource])) {
						$master[$addResource] = true;
						$update = true;
					}
				}
				
				// update master record
				if (!$update) break;
				
				// $cas is null, if the key did not exist
				if ($cas !== null) {
					if ($this->memcached->cas($cas, 'master', $master)) break;
				}
				else {
					if ($this->memcached->set('master', $master)) break;
				}
				
				throw new SystemException('Unable to perform write to master: '.$this->memcached->getResultMessage());
			}
			catch (SystemException $e) {
				// allow at most 5 failures
				if (++$i === 5) {
					throw $e;
				}
			}
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
		$master = $this->memcached->get('master');
		
		if (is_array($master)) {
			foreach ($master as $key => $dummy) {
				if (preg_match($pattern, $key)) {
					$resources[] = $key;
				}
			}
		}
		
		return $resources;
	}
}
