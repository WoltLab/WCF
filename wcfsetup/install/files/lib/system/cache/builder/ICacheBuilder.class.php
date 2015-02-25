<?php
namespace wcf\system\cache\builder;

/**
 * A cache builder provides data for the cache handler that ought to be cached.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
interface ICacheBuilder {
	/**
	 * Returns the data that ought to be cached.
	 * 
	 * @param	array		$parameters
	 * @param	string		$arrayIndex
	 * @return	array
	 */
	public function getData(array $parameters = array(), $arrayIndex = '');
	
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
	public function reset(array $parameters = array());
}
