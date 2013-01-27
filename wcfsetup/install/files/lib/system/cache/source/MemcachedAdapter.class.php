<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Provides a global adapter for accessing the memcached server.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
class MemcachedAdapter extends SingletonFactory {
	/**
	 * memcached object
	 * @var	\Memcached
	 */
	private $memcached = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		if (!class_exists('Memcached')) {
			throw new SystemException('memcached support is not enabled.');
		}
		
		// init memcached
		if (CACHE_SOURCE_MEMCACHED_USE_PCONNECT) {
			$this->memcached = new \Memcached('wcf_memcached');
		}
		else {
			$this->memcached = new \Memcached();
		}
		
		// add servers
		$tmp = explode("\n", StringUtil::unifyNewlines(CACHE_SOURCE_MEMCACHED_HOST));
		$servers = array();
		$defaultWeight = floor(100 / count($tmp));
		foreach ($tmp as $server) {
			$server = StringUtil::trim($server);
			if (!empty($server)) {
				$host = $server;
				$port = 11211; // default memcached port
				$weight = $defaultWeight;
				
				// get port
				if (strpos($host, ':')) {
					$parsedHost = explode(':', $host);
					$host = $parsedHost[0];
					$port = $parsedHost[1];
					
					if (isset($parsedHost[2])) {
						$weight = $parsedHost[2];
					}
				}
				
				$servers[] = array($host, $port, $weight);
			}
		}
		
		$this->memcached->addServers($servers);
		
		// test connection
		$this->memcached->get('testing');
	}
	
	/**
	 * Returns the memcached object.
	 *
	 * @return	\Memcached
	 */
	public function getMemcached() {
		return $this->memcached;
	}
}
