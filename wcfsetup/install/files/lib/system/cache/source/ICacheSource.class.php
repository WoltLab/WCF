<?php
namespace wcf\system\cache\source;

/**
 * Any cache sources should implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Source
 */
interface ICacheSource {
	/**
	 * Flushes a specific cache, optionally removing caches which share the same name.
	 * 
	 * @param	string		$cacheName
	 * @param	bool		$useWildcard
	 */
	public function flush($cacheName, $useWildcard);
	
	/**
	 * Clears the cache completely.
	 */
	public function flushAll();
	
	/**
	 * Returns a cached variable.
	 * 
	 * @param	string		$cacheName
	 * @param	int		$maxLifetime
	 * @return	mixed
	 */
	public function get($cacheName, $maxLifetime);
	
	/**
	 * Stores a variable in the cache.
	 * 
	 * @param	string		$cacheName
	 * @param	mixed		$value
	 * @param	int		$maxLifetime
	 */
	public function set($cacheName, $value, $maxLifetime);
}
