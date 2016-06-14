<?php
namespace wcf\system\cache;
use wcf\system\cache\builder\ICacheBuilder;
use wcf\system\cache\source\DiskCacheSource;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Manages transparent cache access.
 * 
 * @author	Alexander Ebert, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache
 */
class CacheHandler extends SingletonFactory {
	/**
	 * cache source object
	 * @var	\wcf\system\cache\source\ICacheSource
	 */
	protected $cacheSource = null;
	
	/**
	 * Creates a new CacheHandler object.
	 */
	protected function init() {
		// init cache source object
		try {
			$className = 'wcf\system\cache\source\\'.ucfirst(CACHE_SOURCE_TYPE).'CacheSource';
			if (class_exists($className)) {
				$this->cacheSource = new $className();
			}
			else {
				// fallback to disk cache
				$this->cacheSource = new DiskCacheSource();
			}
		}
		catch (SystemException $e) {
			if (CACHE_SOURCE_TYPE != 'disk') {
				// fallback to disk cache
				$this->cacheSource = new DiskCacheSource();
			}
			else {
				throw $e;
			}
		}
	}
	
	/**
	 * Flush cache for given resource.
	 * 
	 * @param	\wcf\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 */
	public function flush(ICacheBuilder $cacheBuilder, array $parameters) {
		$this->getCacheSource()->flush($this->getCacheName($cacheBuilder, $parameters), empty($parameters));
	}
	
	/**
	 * Flushes the entire cache.
	 */
	public function flushAll() {
		$this->getCacheSource()->flushAll();
	}
	
	/**
	 * Returns cached value for given resource, false if no cache exists.
	 * 
	 * @param	\wcf\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 * @return	mixed
	 */
	public function get(ICacheBuilder $cacheBuilder, array $parameters) {
		return $this->getCacheSource()->get($this->getCacheName($cacheBuilder, $parameters), $cacheBuilder->getMaxLifetime());
	}
	
	/**
	 * Caches a value for given resource,
	 * 
	 * @param	\wcf\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 * @param	array						$data
	 */
	public function set(ICacheBuilder $cacheBuilder, array $parameters, array $data) {
		$this->getCacheSource()->set($this->getCacheName($cacheBuilder, $parameters), $data, $cacheBuilder->getMaxLifetime());
	}
	
	/**
	 * Returns cache index hash.
	 * 
	 * @param	array		$parameters
	 * @return	string
	 */
	public function getCacheIndex(array $parameters) {
		return sha1(serialize($this->orderParameters($parameters)));
	}
	
	/**
	 * Builds cache name.
	 * 
	 * @param	\wcf\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 * @return	string
	 */
	protected function getCacheName(ICacheBuilder $cacheBuilder, array $parameters = []) {
		$className = explode('\\', get_class($cacheBuilder));
		$application = array_shift($className);
		$cacheName = str_replace('CacheBuilder', '', array_pop($className));
		if (!empty($parameters)) {
			$cacheName .= '-' . $this->getCacheIndex($parameters);
		}
		
		return $application . '_' . StringUtil::firstCharToUpperCase($cacheName);
	}
	
	/**
	 * Returns the cache source object.
	 * 
	 * @return	\wcf\system\cache\source\ICacheSource
	 */
	public function getCacheSource() {
		return $this->cacheSource;
	}
	
	/**
	 * Unifys parameter order, numeric indizes will be discarded.
	 * 
	 * @param	array		$parameters
	 * @return	array
	 */
	protected function orderParameters($parameters) {
		if (!empty($parameters)) {
			array_multisort($parameters);
		}
		
		return $parameters;
	}
}
