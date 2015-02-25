<?php
namespace wcf\system\cache\builder;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Default implementation for cache builders.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
abstract class AbstractCacheBuilder extends SingletonFactory implements ICacheBuilder {
	/**
	 * list of cache resources by index
	 * @var	array<array>
	 */
	protected $cache = array();
	
	/**
	 * maximum cache lifetime in seconds, '0' equals infinite
	 * @var	integer
	 */
	protected $maxLifetime = 0;
	
	/**
	 * @see	\wcf\system\cache\builder\ICacheBuilder::getData()
	 */
	public function getData(array $parameters = array(), $arrayIndex = '') {
		$index = CacheHandler::getInstance()->getCacheIndex($parameters);
		
		if (!isset($this->cache[$index])) {
			// fetch cache or rebuild if missing
			$this->cache[$index] = CacheHandler::getInstance()->get($this, $parameters);
			if ($this->cache[$index] === null) {
				$this->cache[$index] = $this->rebuild($parameters);
				
				// update cache
				CacheHandler::getInstance()->set($this, $parameters, $this->cache[$index]);
			}
		}
		
		if (!empty($arrayIndex)) {
			if (!isset($this->cache[$index][$arrayIndex])) {
				throw new SystemException("array index '".$arrayIndex."' does not exist in cache resource");
			}
			
			return $this->cache[$index][$arrayIndex];
		}
		
		return $this->cache[$index];
	}
	
	/**
	 * @see	\wcf\system\cache\builder\ICacheBuilder::getMaxLifetime()
	 */
	public function getMaxLifetime() {
		return $this->maxLifetime;
	}
	
	/**
	 * @see	\wcf\system\cache\builder\ICacheBuilder::reset()
	 */
	public function reset(array $parameters = array()) {
		CacheHandler::getInstance()->flush($this, $parameters);
	}
	
	/**
	 * Rebuilds cache for current resource.
	 * 
	 * @param	array		$parameters
	 */
	abstract protected function rebuild(array $parameters);
}
