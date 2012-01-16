<?php
namespace wcf\system\request;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\route\RouteHandler;
use wcf\system\route\IRouteController;
use wcf\system\SingletonFactory;

/**
 * Handles relative links within the wcf.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
class LinkHandler extends SingletonFactory {
	/**
	 * Returns a relative link.
	 * 
	 * @param	string		$controller
	 * @param 	array		$parameters
	 * @param 	string		$url
	 * @return	string
	 */
	public function getLink($controller = null, array $parameters = array(), $url = '') {
		$abbreviation = 'wcf';
		$isRaw = false;
		if (isset($parameters['application'])) {
			$abbreviation = $parameters['application'];
			unset($parameters['application']);
		}
		if (isset($parameters['isRaw'])) {
			$isRaw = $parameters['isRaw'];
			unset($parameters['isRaw']);
		}
		
		$routeURL = "";
		if (is_string($controller)) {
			$parameters['controller'] = $controller;			
			$routeURL = RouteHandler::getInstance()->buildRoute(array_map(array($this, 'prepareString'), $parameters));
		}
		else if ($controller instanceof IRouteController || ($controller instanceof DatabaseObjectDecorator && $controller->getDecoratedObject() instanceof IRouteController)) {
			if ($controller instanceof DatabaseObjectDecorator && $controller->getDecoratedObject() instanceof IRouteController) {
				$controller = $controller->getDecoratedObject();
			}
			
			$route = RouteHandler::getInstance()->getRouteByName($controller->getRouteName());
			if ($route === null) {
				throw new SystemException("Unknown route '".$controller->getRouteName()."'");
			}
			
			$routeURL = $route->buildLink(array_map(array($this, 'prepareString'), $controller->getRouteComponentValues()));
		}
		
		if ($routeURL != '' && !$isRaw && !empty($url)) {
			$routeURL .= (strpos($routeURL, '?') === false) ? '?' : '&';
		}
		$url = $routeURL . $url;
		
		// append session id
		$url .= (strpos($url, '?') === false) ? SID_ARG_1ST : SID_ARG_2ND_NOT_ENCODED;
		
		// handle application groups
		$applicationGroup = ApplicationHandler::getInstance()->getActiveGroup();
		if ($applicationGroup !== null) {
			// try to resolve abbreviation
			$application = null;
			if ($abbreviation != 'wcf') {
				$application = ApplicationHandler::getInstance()->getApplication($abbreviation);
			}
			
			// fallback to primary application if abbreviation is 'wcf' or unknown
			if ($application === null) {
				$application = ApplicationHandler::getInstance()->getPrimaryApplication();
			}
			
			$url = $application->domainName.$application->domainPath.(RequestHandler::getInstance()->isACP() ? 'acp/' : '').$url;
		}
		
		return $url;
	}
	
	/**
	 * Prepares a string to be used in a link by removing illegal characters.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function prepareString($string) {
		return trim(preg_replace('/[\x0-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+/', '-', $string), '-');
	}
}
