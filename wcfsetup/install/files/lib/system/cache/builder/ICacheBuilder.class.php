<?php
namespace wcf\system\cache\builder;

/**
 * A cache builder provides data for the cache handler that ought to be cached.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category	Community Framework
 */
interface ICacheBuilder {
	/**
	 * Returns the data that ought to be cached.
	 * 
	 * @param	array		$cacheResource
	 * @return	array
	 */
	public function getData(array $cacheResource);
}
