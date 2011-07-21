<?php
namespace wcf\system\cache\source;

/**
 * Any cache sources should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category 	Community Framework
 */
interface ICacheSource {
	/**
	 * Returns a cached variable.
	 *
	 * @param	array		$cacheResource
	 * @return	mixed
	 */
	public function get(array $cacheResource);
	
	/**
	 * Stores a variable in the cache.
	 *
	 * @param	array		$cacheResource
	 * @param 	mixed		$value
	 */
	public function set(array $cacheResource, $value);
	
	/**
	 * Deletes a variable in the cache.
	 *
	 * @param	array		$cacheResource
	 * @param 	boolean		$ignoreLifetime
	 */
	public function delete(array $cacheResource, $ignoreLifetime = false);
	
	/**
	 * Marks cached files as obsolete.
	 *
	 * @param 	string 		$directory
	 * @param 	string 		$filepattern
	 * @param 	boolean		$forceDelete
	 */
	public function clear($directory, $filepattern, $forceDelete = false);
	
	/**
	 * Closes this cache source.
	 */
	public function close();
	
	/**
	 * Clears the cache completely.
	 */
	public function flush();
}
