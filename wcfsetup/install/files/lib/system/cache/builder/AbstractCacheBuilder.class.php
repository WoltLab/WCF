<?php
namespace wcf\system\cache\builder;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Default implementation for cache builders.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
abstract class AbstractCacheBuilder extends SingletonFactory implements ICacheBuilder {
	/**
	 * list of cache resources by index
	 * @var	mixed[][]
	 */
	protected $cache = [];
	
	/**
	 * maximum cache lifetime in seconds, '0' equals infinite
	 * @var	integer
	 */
	protected $maxLifetime = 0;
	
	/**
	 * @inheritDoc
	 */
	public function getData(array $parameters = [], $arrayIndex = '') {
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
			if (!array_key_exists($arrayIndex, $this->cache[$index])) {
				throw new SystemException("array index '".$arrayIndex."' does not exist in cache resource");
			}
			
			return $this->cache[$index][$arrayIndex];
		}
		
		return $this->cache[$index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxLifetime() {
		return $this->maxLifetime;
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset(array $parameters = []) {
		CacheHandler::getInstance()->flush($this, $parameters);
	}
	
	/**
	 * Rebuilds cache for current resource.
	 * 
	 * @param	array		$parameters
	 */
	abstract protected function rebuild(array $parameters);
}
