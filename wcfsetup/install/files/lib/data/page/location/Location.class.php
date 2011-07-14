<?php
/**
 * Any page location class should implement this interface.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.location
 * @category 	Community Framework
 */
interface Location {
	/**
	 * Caches the information of a page location.
	 * 
	 * @param	array		$location
	 * @param	string		$requestURI
	 * @param	string		$requestMethod
	 * @param	array		$match
	 */
	public function cache($location, $requestURI, $requestMethod, $match);
	
	/**
	 * Returns the information of a page location.
	 * 
	 * @param	array		$location
	 * @param	string		$requestURI
	 * @param	string		$requestMethod
	 * @param	array		$match
	 * @return	string
	 */
	public function get($location, $requestURI, $requestMethod, $match);
}
