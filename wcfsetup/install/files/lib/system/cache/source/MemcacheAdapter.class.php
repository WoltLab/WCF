<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Provides a global adapter for accessing the memcache server.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category 	Community Framework
 */
class MemcacheAdapter extends SingletonFactory {
	/**
	 * memcache object
	 *
	 * @var Memcache
	 */
	private $memcache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		if (!class_exists('Memcache')) {
			throw new SystemException('memcache support is not enabled.');
		}

		// init memcache
		$this->memcache = new \Memcache();
		
		// add servers
		$servers = explode("\n", StringUtil::unifyNewlines(CACHE_SOURCE_MEMCACHE_HOST));
		foreach ($servers as $server) {
			$server = StringUtil::trim($server);
			if (!empty($server)) {
				$host = $server;
				$port = 11211; // default memcache port
				// get port
				if (strpos($host, ':')) {
					$parsedHost = explode(':', $host);
					$host = $parsedHost[0];
					$port = $parsedHost[1];
				}
				
				$this->memcache->addServer($host, $port, CACHE_SOURCE_MEMCACHE_USE_PCONNECT);
			}
		}
		
		// test connection
		$this->memcache->get('testing');
	}
	
	/**
	 * Returns the memcache object.
	 *
	 * @return Memcache
	 */
	public function getMemcache() {
		return $this->memcache;
	}
}
