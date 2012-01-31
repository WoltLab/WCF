<?php
namespace wcf\system\page\location;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\page\location\PageLocation;

/**
 * Any page location class should implement this interface.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.page.location
 * @category 	Community Framework
 */
interface IPageLocation extends IDatabaseObjectProcessor {
	/**
	 * Caches the information of a page location.
	 * 
	 * @param	wcf\data\page\location\PageLocation	$location
	 * @param	string		$requestURI
	 * @param	string		$requestMethod
	 * @param	array		$match
	 */
	public function cache(PageLocation $location, $requestURI, $requestMethod, array $match);
	
	/**
	 * Returns the information of a page location.
	 * 
	 * @param	wcf\data\page\location\PageLocation	$location
	 * @param	string		$requestURI
	 * @param	string		$requestMethod
	 * @param	array		$match
	 * @return	string
	 */
	public function get(PageLocation $location, $requestURI, $requestMethod, array $match);
}
