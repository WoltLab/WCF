<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * ApcuCacheSource is an implementation of CacheSource that uses APCu to store cached variables.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
/* modified by Jan Altensen (Stricted) */
class ApcuCacheSource implements ICacheSource {
	/**
	 * key prefix
	 * @var	string
	 */
	protected $prefix = '';
	
	/**
	 * Creates a new ApcCacheSource object.
	 */
	public function __construct() {
		if (!function_exists('apcu_store')) {
			throw new SystemException('APCu support is not enabled.');
		}
		
		// set variable prefix to prevent collision
		$this->prefix = 'WCF_'.substr(sha1(WCF_DIR), 0, 10) . '_';
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush($cacheName, $useWildcard) {
		if ($useWildcard) {
			$this->removeKeys($this->prefix . $cacheName . '(\-[a-f0-9]+)?');
		}
		else {
			apcu_delete($this->prefix . $cacheName);
		}
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flushAll()
	 */
	public function flushAll() {
		$this->removeKeys();
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::get()
	 */
	public function get($cacheName, $maxLifetime) {
		if (($data = apcu_fetch($this->prefix . $cacheName)) === false) {
			return null;
		}
		
		return $data;
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::set()
	 */
	public function set($cacheName, $value, $maxLifetime) {
		apcu_store($this->prefix . $cacheName, $value, $this->getTTL($maxLifetime));
	}
	
	/**
	 * Returns time to live in seconds, defaults to 3 days.
	 * 
	 * @param	integer		$maxLifetime
	 * @return	integer
	 */
	protected function getTTL($maxLifetime = 0) {
		if ($maxLifetime) {
			// max lifetime is a timestamp, discard (similar to http://www.php.net/manual/en/memcached.expiration.php)
			if ($maxLifetime > (60 * 60 * 24 * 30)) {
				$maxLifetime = 0;
			}
		}
		
		if ($maxLifetime) {
			return $maxLifetime;
		}
		
		// default TTL: 3 days
		return (60 * 60 * 24 * 3);
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::clear()
	 */
	public function removeKeys($pattern = null) {
		$regex = null;
		if ($pattern !== null) {
			$regex = new Regex('^'.$pattern.'$');
		}
		
		$apcuCacheInfo = apcu_cache_info('user');
		foreach ($apcuCacheInfo['cache_list'] as $cache) {
			#die(print_r($cache));
			if ($regex === null) {
				if (StringUtil::startsWith($cache['key'], $this->prefix)) {
					apcu_delete($cache['key']);
				}
			}
			else if ($regex->match($cache['key'])) {
				apcu_delete($cache['key']);
			}
		}
	}
}
