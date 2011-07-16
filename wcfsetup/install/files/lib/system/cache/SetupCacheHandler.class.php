<?php
namespace wcf\system\cache;

/**
 * Disables cache functions during the wcf setup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class SetupCacheHandler extends CacheHandler {
	/**
	 * @see wcf\system\cache\CacheHandler::addResource()
	 */
	public function addResource($cache, $file, $classFile, $minLifetime = 0, $maxLifetime = 0) {
		return false;
	}
	
	/**
	 * @see wcf\system\cache\CacheHandler::clearResource()
	 */
	public function clearResource($cache, $ignoreLifetime = false) {
		return false;
	}
	
	/**
	 * @see wcf\system\cache\CacheHandler::clear()
	 */
	public function clear($directory, $filepattern, $forceDelete = false) {
		return false;
	}
	
	/**
	 * @see wcf\system\cache\CacheHandler::get()
	 */
	public function get($cache, $variable = '') {
		return false;
	}
	
	/**
	 * @see wcf\system\cache\CacheHandler::load()
	 */
	public function load($cacheResource, $reload = false) {
		return false;
	}
	
	/**
	 * @see wcf\system\cache\CacheHandler::rebuild()
	 */
	public function rebuild($cacheResource, $forceRebuilt = false) {
		return false;
	}
}
