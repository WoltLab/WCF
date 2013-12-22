<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * @author		Jan Altensen (Stricted)
 * @copyright	2013 Jan Altensen (Stricted)
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		be.bastelstu.jan.wcf.apcu
 * @subpackage	util
 * @category	Community Framework
 */
class APCUtil {
	/**
	 * php extension
	 * @var	string
	 */
	protected $extension = "";
	
	/**
	 * APC(u) version
	 * @var integer
	 */
	public $version = 0;
	
	/**
	 * Creates a new APCUtil object.
	 */
	public function __construct () {
		if (extension_loaded("apcu")) {
			$this->extension = "apcu";
			$this->version = phpversion('apcu');
		} else if (extension_loaded("apc")) {
			$this->extension = "apc";
			$this->version = phpversion('apc');
		} else
			throw new SystemException('APC support is not enabled.');
	}
	
	/**
	 * deletes a cache item
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public function delete ($key) {
		if ($this->extension == "apcu")
			return apcu_delete($key);
		else
			return apc_delete($key);
	}
	
	/**
	 * fetch a cache item
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public function fetch ($key) {
		if ($this->extension == "apcu")
			return apcu_fetch($key);
		else
			return apc_fetch($key);
	}
	
	/**
	 * store a cache item
	 *
	 * @param	string	$key
	 * @param	string	$var
	 * @param	integer	$ttl
	 * @return	boolean
	 */
	public function store ($key, $var, $ttl) {
		if ($this->extension == "apcu")
			return apcu_store($key, $var, $ttl);
		else
			return apc_store($key, $var, $ttl);
	}
	
	/**
	 * get cache items
	 *
	 * @param	string	$key
	 * @return	array
	 */
	public function cache_info ($key = "user") {
		$info = array();
		if ($this->extension == "apcu") {
			$apcinfo = apcu_cache_info($key);
			$cacheList = $apcinfo['cache_list'];
			
			usort($cacheList, function ($a, $b) {
				return $a['key'] > $b['key'];
			});
			
			foreach ($cacheList as $cache) {
				$apcu = $cache;
				$apcu['info'] = $cache['key'];
				$info[] = $apcu;
			}
		} else {
			$apcinfo = apc_cache_info($key);
			$cacheList = $apcinfo['cache_list'];
			
			usort($cacheList, function ($a, $b) {
				return $a['info'] > $b['info'];
			});
			
			foreach ($cacheList as $cache) {
				$info[] = $cache;
			}
		}
		
		return $info;
	}
}
