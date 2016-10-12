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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Source
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
		
		$this->memcached->setOption(\Memcached::OPT_PREFIX_KEY, WCF_UUID.'_');
		
		if (!WCF::debugModeIsEnabled()) {
			// use the more efficient binary protocol to communicate with the memcached instance
			// this option is disabled in debug mode to allow for easier debugging
			// with tools, such as strace(1)
			$this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
		}
		
		// add servers
		$tmp = explode("\n", StringUtil::unifyNewlines(CACHE_SOURCE_MEMCACHED_HOST));
		$servers = [];
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
				
				$servers[] = [$host, $port, $weight];
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
	 * @inheritDoc
	 */
	public function flush($cacheName, $useWildcard) {
		if ($useWildcard) {
			$this->memcached->add('_flush_'.$cacheName, TIME_NOW);
			$this->memcached->increment('_flush_'.$cacheName);
		}
		
		$this->memcached->delete($this->getCacheName($cacheName));
	}
	
	/**
	 * @inheritDoc
	 */
	public function flushAll() {
		// increment flush counter to nuke all data
		$this->memcached->add('_flush', TIME_NOW);
		$this->memcached->increment('_flush');
	}
	
	/**
	 * @inheritDoc
	 */
	public function get($cacheName, $maxLifetime) {
		$value = $this->memcached->get($this->getCacheName($cacheName));
		
		if ($value === false) {
			// check if value does not exist
			if ($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
				return null;
			}
		}
		
		return $value;
	}
	
	/**
	 * @inheritDoc
	 */
	public function set($cacheName, $value, $maxLifetime) {
		$this->memcached->set($this->getCacheName($cacheName), $value, $this->getTTL($maxLifetime));
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
	 * Returns the name for the given cache name in respect to flush count.
	 * 
	 * @param	string		$cacheName
	 * @return	string
	 */
	protected function getCacheName($cacheName) {
		$parts = explode('-', $cacheName, 2);
		
		$flush = $this->memcached->get('_flush');
		
		// create flush counter if it does not exist
		if ($flush === false) {
			$this->memcached->add('_flush', TIME_NOW);
			$flush = $this->memcached->get('_flush');
		}
		
		// the cache specific flush counter only is of interest if the cache name contains parameters
		// the version without parameters is deleted explicitly when calling flush
		// this saves us a memcached query in most cases (caches without any parameters)
		if (isset($parts[1])) {
			$flushByCache = $this->memcached->get('_flush_'.$parts[0]);
			
			// create flush counter if it does not exist
			if ($flushByCache === false) {
				$this->memcached->add('_flush_'.$parts[0], TIME_NOW);
				$flushByCache = $this->memcached->get('_flush_'.$parts[0]);
			}
			
			$flush .= '_'.$flushByCache;
		}
		
		return $flush.'_'.$cacheName;
	}
}
