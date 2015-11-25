<?php
namespace wcf\system\request\route;
use wcf\system\request\IRoute;

/**
 * Default interface for route implementations.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2015 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.wcf
 * @subpackage  system.request
 * @category    Community Framework
 */
interface IRequestRoute {
	/**
	 * Builds a link upon route components.
	 *
	 * @param	array		$components     list of url components
	 * @return	string
	 */
	public function buildLink(array $components);
	
	/**
	 * Returns true if current route can handle the build request.
	 *
	 * @param	array		$components     list of url components
	 * @return	boolean
	 */
	public function canHandle(array $components);
	
	/**
	 * Returns parsed route data.
	 *
	 * @return	array
	 */
	public function getRouteData();
	
	/**
	 * Returns true if route applies for ACP.
	 *
	 * @return	boolean
	 */
	public function isACP();
	
	/**
	 * Returns true if given request url matches this route.
	 *
	 * @param       string          $application    application identifier
	 * @param	string		$requestURL     request url
	 * @return	boolean
	 */
	public function matches($application, $requestURL);
	
	/**
	 * Configures this route to handle either ACP or frontend requests.
	 * 
	 * @param boolean $isACP true if route handles ACP requests
	 */
	public function setIsACP($isACP);
}
