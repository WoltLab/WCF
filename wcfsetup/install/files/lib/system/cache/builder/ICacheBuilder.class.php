<?php
namespace wcf\system\cache\builder;

/**
 * A cache builder provides data for the cache handler that ought to be cached.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
interface ICacheBuilder {
	/**
	 * Returns the data that ought to be cached.
	 * 
	 * @param	array		$parameters
	 * @param	string		$arrayIndex
	 * @return	mixed
	 */
	public function getData(array $parameters = [], $arrayIndex = '');
	
	/**
	 * Returns maximum lifetime for cache resource.
	 * 
	 * @return	integer
	 */
	public function getMaxLifetime();
	
	/**
	 * Flushes cache. If no parameters are given, all caches starting with
	 * the same cache name will be flushed too.
	 * 
	 * @param	array		$parameters
	 */
	public function reset(array $parameters = []);
}
