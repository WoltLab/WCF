<?php
namespace wcf\system\request;

/**
 * Default interface for route implementations.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 * @deprecated 2.2:2.3 will be replaced with \wcf\system\request\route\IRequestRoute
 */
interface IRoute {
	/**
	 * Builds a link upon route components.
	 * 
	 * @param	array		$components
	 * @return	string
	 */
	public function buildLink(array $components);
	
	/**
	 * Returns true if current route can handle the build request.
	 * 
	 * @param	array		$components
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
	 * @param	string		$requestURL
	 * @return	boolean
	 */
	public function matches($requestURL);
}
