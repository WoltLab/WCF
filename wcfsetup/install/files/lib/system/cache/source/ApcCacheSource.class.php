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
class ApcCacheSource implements ICacheSource {
	/**
	 * Creates a new ApcCacheSource object.
	 */
	public function __construct() {
		if (!function_exists('apc_store')) {
			throw new SystemException('APC support is not enabled.');
		}
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::get()
	 */
	public function get(array $cacheResource) {
		if (($data = apc_fetch($cacheResource['file'])) === false) {
			return null;
		}
		
		return $data;
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::set()
	 */
	public function set(array $cacheResource, $value) {
		apc_store($cacheResource['file'], $value, $cacheResource['maxLifetime']);
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::delete()
	 */
	public function delete(array $cacheResource) {
		apc_delete($cacheResource['file']);
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::clear()
	 */
	public function clear($directory, $filepattern) {
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
	 * @see	wcf\system\cache\source\ICacheSource::close()
	 */
	public function close() {
		// does nothing
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush() {
		apc_clear_cache('user');
	}
}
