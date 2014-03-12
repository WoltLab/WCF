<?php
namespace wcf\system\cache\source;
use wcf\util\StringUtil;

/**
 * NoCacheSource is an implementation of CacheSource that does not store any data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
class NoCacheSource implements ICacheSource {
	/**
	 * list of cached values
	 * @var	array<array>
	 */
	protected $cache = array();
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush($cacheName, $useWildcard) {
		if (isset($this->cache[$cacheName])) {
			unset($this->cache[$cacheName]);
		}
		
		if ($useWildcard) {
			$cacheName .= '-';
			foreach (array_keys($this->cache) as $key) {
				if (StringUtil::startsWith($key, $cacheName)) {
					unset($this->cache[$key]);
				}
			}
		} 
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::flushAll()
	 */
	public function flushAll() {
		$this->cache = array();
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::get()
	 */
	public function get($cacheName, $maxLifetime) {
		if (isset($this->cache[$cacheName])) {
			return $this->cache[$cacheName];
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\cache\source\ICacheSource::set()
	 */
	public function set($cacheName, $value, $maxLifetime) {
		$this->cache[$cacheName] = $value;
	}
}
