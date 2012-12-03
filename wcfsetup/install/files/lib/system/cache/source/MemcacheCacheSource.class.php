<?php
namespace wcf\system\cache\source;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * MemcacheCacheSource is an implementation of CacheSource that uses a Memcache server to store cached variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category	Community Framework
 */
class MemcacheCacheSource implements ICacheSource {
	/**
	 * MemcacheAdapter object
	 * @var	wcf\system\cache\source\MemcacheAdapter
	 */
	protected $adapter = null;
	
	/**
	 * list of cache resources
	 * @var	array<string>
	 */
	protected $cacheResources = null;
	
	/**
	 * list of new cache resources
	 * @var	array<string>
	 */
	protected $newLogEntries = array();
	
	/**
	 * list of obsolete resources
	 * @var	array<string>
	 */
	protected $obsoleteLogEntries = array();
	
	/**
	 * Creates a new MemcacheCacheSource object.
	 */
	public function __construct() {
		$this->adapter = MemcacheAdapter::getInstance();
	}
	
	/**
	 * Returns the memcache adapter.
	 * 
	 * @return	MemcacheAdapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}
	
	// internal log functions
	/**
	 * Loads the cache log.
	 */
	protected function loadLog() {
		if ($this->cacheResources === null) {
			$this->cacheResources = array();
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_cache_resource";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				$this->cacheResources[] = $row['cacheResource'];
			}
		}
	}
	
	/**
	 * Saves modifications of the cache log.
	 */
	protected function updateLog() {
		if (!empty($this->newLogEntries)) {
			$sql = "DELETE FROM	wcf".WCF_N."_cache_resource
				WHERE		cacheResource = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->newLogEntries as $entry) {
				$statement->execute(array($entry));
			}
			
			$sql = "INSERT INTO	wcf".WCF_N."_cache_resource
						(cacheResource)
				VALUES		(?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->newLogEntries as $entry) {
				$statement->execute(array($entry));
			}
			
		}
		
		if (!empty($this->obsoleteLogEntries)) {
			$sql = "DELETE FROM	wcf".WCF_N."_cache_resource
				WHERE		cacheResource = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->obsoleteLogEntries as $entry) {
				$statement->execute(array($entry));
			}
		}
	}
	
	/**
	 * Adds a cache resource to cache log.
	 * 
	 * @param	string		$cacheResource
	 */
	protected function addToLog($cacheResource) {
		$this->newLogEntries[] = $cacheResource;
	}
	
	/**
	 * Removes an obsolete cache resource from cache log.
	 * 
	 * @param	string		$cacheResource
	 */
	protected function removeFromLog($cacheResource) {
		$this->obsoleteLogEntries[] = $cacheResource;
	}
	
	// CacheSource implementations
	/**
	 * @see	wcf\system\cache\source\ICacheSource::get()
	 */
	public function get(array $cacheResource) {
		$value = $this->getAdapter()->getMemcache()->get($cacheResource['file']);
		if ($value === false) return null;
		return $value;
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::set()
	 */
	public function set(array $cacheResource, $value) {
		$this->getAdapter()->getMemcache()->set($cacheResource['file'], $value, MEMCACHE_COMPRESSED, $cacheResource['maxLifetime']);
		$this->addToLog($cacheResource['file']);
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::delete()
	 */
	public function delete(array $cacheResource) {
		$this->getAdapter()->getMemcache()->delete($cacheResource['file']);
		$this->removeFromLog($cacheResource['file']);
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::clear()
	 */
	public function clear($directory, $filepattern) {
		$this->loadLog();
		$pattern = preg_quote(FileUtil::addTrailingSlash($directory), '%').str_replace('*', '.*', str_replace('.', '\.', $filepattern));
		foreach ($this->cacheResources as $cacheResource) {
			if (preg_match('%^'.$pattern.'$%i', $cacheResource)) {
				$this->getAdapter()->getMemcache()->delete($cacheResource);
				$this->removeFromLog($cacheResource);
			}
		}
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush() {
		// clear cache
		$this->getAdapter()->getMemcache()->flush();
		
		// clear log
		$this->newLogEntries = $this->obsoleteLogEntries = array();
		
		$sql = "DELETE FROM	wcf".WCF_N."_cache_resource";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::close()
	 */
	public function close() {
		// update log
		$this->updateLog();
		
		// close connection
		if ($this->getAdapter() !== null && $this->getAdapter()->getMemcache() !== null) $this->getAdapter()->getMemcache()->close();
	}
}
