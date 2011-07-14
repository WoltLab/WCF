<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * ApcCacheSource is an implementation of CacheSource that uses APC to store cached variables.
 * 
 * @author	Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category 	Community Framework
 */
class ApcCacheSource implements CacheSource {
	/**
	 * Creates a new ApcCacheSource object.
	 */
	public function __construct() {
		if (!function_exists('apc_store')) {
			throw new SystemException('APC support is not enabled.');
		}
	}
	
	/**
	 * @see CacheSource::get()
	 */
	public function get($cacheResource) {
		if (false === $data = apc_fetch($cacheResource['file'])) {
			return null;
		}
		
		return $data;
	}
	
	/**
	 * @see CacheSource::set()
	 */
	public function set($cacheResource, $value) {
		apc_store($cacheResource['file'], $value, $cacheResource['maxLifetime']);
	}
	
	/**
	 * @see CacheSource::delete()
	 */
	public function delete($cacheResource, $ignoreLifetime = false) {
		if ($ignoreLifetime || ($cacheResource['minLifetime'] == 0 || $this->checkMinLifetime($cacheResource))) {
			apc_delete($cacheResource['file']);
		}
	}
	
	/**
	 * Checks if the minimum lifetime is expired.
	 * 
	 * @param	array		$cacheResource
	 */
	public function checkMinLifetime($cacheResource) {
		$apcinfo = apc_cache_info('user');
		$cacheList = $apcinfo['cache_list'];
		
		foreach ($cacheList as $cache) {
			if ($cache['info'] == $cacheResource['file']) {
				return ((TIME_NOW - $cache['mtime']) >= $cacheResource['minLifetime']);
			}
		}
		
		return true;
	}
	
	/**
	 * @see CacheSource::clear()
	 */
	public function clear($directory, $filepattern, $forceDelete = false) {
		$pattern = preg_quote(FileUtil::addTrailingSlash($directory), '%').str_replace('*', '.*', str_replace('.', '\.', $filepattern));
		
		$apcinfo = apc_cache_info('user');
		$cacheList = $apcinfo['cache_list'];
		foreach ($cacheList as $cache) {
			if (preg_match('%^'.$pattern.'$%i', $cache['info'])) {
				apc_delete($cache['info']);
			}
		}
	}
	
	/**
	 * @see CacheSource::close()
	 */
	public function close() {
		// does nothing
	}
	
	/**
	 * @see CacheSource::flush()
	 */
	public function flush() {
		apc_clear_cache('user');
	}
}
?>
