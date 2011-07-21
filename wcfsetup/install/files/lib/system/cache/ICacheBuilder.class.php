<?php
namespace wcf\system\cache;

/**
 * A CacheBuilder provides data to the CacheHandler that ought to be cached.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
interface ICacheBuilder {
	/**
	 * Returns the data that ought to be cached.
	 *
	 * @param 	array 		$cacheResource
	 * @return 	array 		$data
	 */
	public function getData($cacheResource);
}
