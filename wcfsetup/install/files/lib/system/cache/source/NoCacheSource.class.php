<?php
namespace wcf\system\cache\source;

/**
 * NoCacheSource is an implementation of CacheSource that does not store any data.
 * 
 * @author	Tim Düsterhus
 * @copyright	2011 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
class NoCacheSource implements ICacheSource {
	/**
	 * @see wcf\system\cache\source\ICacheSource::get()
	 */
	public function get(array $cacheResource) {
		if (!isset($this->cache[$cacheResource['cache']])) return null;

		return $this->cache[$cacheResource['cache']];
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::set()
	 */
	public function set(array $cacheResource, $value) {
		// we have to keep it temporarily
		$this->cache[$cacheResource['cache']] = $value;
		$this->loaded[$cacheResource['file']] = true;
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::delete()
	 */
	public function delete(array $cacheResource) {	
		// reset open cache
		if (isset($this->cache[$cacheResource['cache']])) unset($this->cache[$cacheResource['cache']]);
		if (isset($this->loaded[$cacheResource['file']])) unset($this->loaded[$cacheResource['file']]);

		return;
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::clear()
	 */
	public function clear($directory, $filepattern) {
		return;
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::close()
	 */
	public function close() {
		return;
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush() {
		return;
	}
}
