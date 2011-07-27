<?php
namespace wcf\system\cache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * CacheHandler holds all registered cache resources.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheHandler extends SingletonFactory {
	/**
	 * Registered cache resources.
	 * 
	 * @var array
	 */
	protected $cacheResources = array();
	
	/**
	 * cache source object
	 * 
	 * @var	wcf\system\cache\source\ICacheSource
	 */
	protected $cacheSource = null;
	
	/**
	 * Creates a new CacheHandler object.
	 */
	protected function init() {
		// init cache source object
		try {
			$className = 'wcf\system\cache\source\\'.ucfirst(CACHE_SOURCE_TYPE).'CacheSource';
			$this->cacheSource = new $className();
		}
		catch (SystemException $e) {
			if (CACHE_SOURCE_TYPE != 'disk') {
				// fallback to disk cache
				$this->cacheSource = new \wcf\system\cache\source\DiskCacheSource();
			}
			else {
				throw $e;
			}
		}
	}
	
	/**
	 * Registers a new cache resource.
	 * 
	 * @param	string		$cache		name of this resource
	 * @param	string		$file		data file for this resource
	 * @param	string		$className
	 * @param	integer		$maxLifetime
	 */
	public function addResource($cache, $file, $className, $maxLifetime = 0) {
		$this->cacheResources[$cache] = array(
			'cache' => $cache,
			'file' => $file, 
			'className' => $className, 
			'maxLifetime' => $maxLifetime
		);
	}
	
	/**
	 * Deletes a registered cache resource.
	 * 
	 * @param 	string		$cache
	 */
	public function clearResource($cache) {
		if (!isset($this->cacheResources[$cache])) {
			throw new SystemException("cache resource '".$cache."' does not exist", 11005);
		}
		
		$this->getCacheSource()->delete($this->cacheResources[$cache]);
	}
	
	/**
	 * Marks cached files as obsolete.
	 *
	 * @param 	string 		$directory
	 * @param 	string 		$filepattern
	 */
	public function clear($directory, $filepattern) {
		$this->getCacheSource()->clear($directory, $filepattern);
	}
	
	/**
	 * Returns a cached variable.
	 *
	 * @param 	string 		$cache
	 * @param 	string 		$variable
	 * @return 	mixed 		$value
	 */
	public function get($cache, $variable = '') {
		if (!isset($this->cacheResources[$cache])) {
			throw new SystemException("unknown cache resource '".$cache."'", 11005);
		}
		
		// try to get value
		$value = $this->getCacheSource()->get($this->cacheResources[$cache]);
		if ($value === null) {
			// rebuild cache
			$this->rebuild($this->cacheResources[$cache]);
			
			// try to get value again
			$value = $this->getCacheSource()->get($this->cacheResources[$cache]);
			if ($value === null) {
				throw new SystemException("cache resource '".$cache."' does not exist", 11005);
			}
		}
		
		// return value
		if (!empty($variable)) {
			if (!isset($value[$variable])) {
				throw new SystemException("variable '".$variable."' does not exist in cache resource '".$cache."'", 11008);
			}
			
			return $value[$variable];
		}
		else {
			return $value;
		}
	}
	
	/**
	 * Rebuilds a cache resource.
	 * 
	 * @param 	array 		$cacheResource
	 * @return 	boolean 	result
	 */
	public function rebuild($cacheResource) {
		// instance cache class
		if (!class_exists($cacheResource['className'])) {
			throw new SystemException("Unable to find class '".$cacheResource['className']."'", 11001);
		}
		
		// update file last modified time to avoid multiple users rebuilding cache at the same time
		if (get_class($this->getCacheSource()) == 'wcf\system\cache\source\DiskCacheSource') {
			@touch($cacheResource['file']);
		}
		
		// build cache
		$cacheBuilder = new $cacheResource['className'];
		$value = $cacheBuilder->getData($cacheResource);

		// save cache
		$this->getCacheSource()->set($cacheResource, $value);
		
		return true;
	}
	
	/**
	 * Returns the cache source object.
	 *
	 * @return	CacheSource
	 */
	public function getCacheSource() {
		return $this->cacheSource;
	}
}
