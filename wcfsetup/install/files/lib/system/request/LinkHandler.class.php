<?php
namespace wcf\system\request;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\application\ApplicationHandler;
use wcf\system\route\IRouteController;
use wcf\system\route\RouteHandler;
use wcf\system\SingletonFactory;

/**
 * Handles relative links within the wcf.
 * 
 * @author 	Matthias Schmidt, Marcel Werk
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
		$anchor = '';
		$isRaw = false;
		if (isset($parameters['application'])) {
			$abbreviation = $parameters['application'];
			unset($parameters['application']);
		}
		if (isset($parameters['isRaw'])) {
			$isRaw = $parameters['isRaw'];
			unset($parameters['isRaw']);
		}
		
		// remove anchor before parsing
		if (($pos = strpos($url, '#')) !== false) {
			$anchor = substr($url, $pos);
			$url = substr($url, 0, $pos);
		}
		
		if (isset($parameters['controllerObject']) && $parameters['controllerObject'] instanceof IRouteController) {
			$object = $parameters['controllerObject'];
			unset($parameters['controllerObject']);
			
			$parameters = array_merge($object->getRouteComponentValues(), $parameters);
		}
		
		// build route
		$routeURL = "";
		if (is_string($controller)) {
			$parameters['controller'] = $controller;
			$routeURL = RouteHandler::getInstance()->buildRoute($parameters);
		}
		else if ($controller instanceof IRouteController) {
			$routeURL = RouteHandler::getInstance()->buildRoute($controller->getRouteComponentValues());
		}
		else if ($controller instanceof DatabaseObjectDecorator && $controller->getDecoratedObject() instanceof IRouteController) {
			$routeURL = RouteHandler::getInstance()->buildRoute($controller->getDecoratedObject()->getRouteComponentValues());
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
			
			$url = $application->domainName.$application->domainPath.(RequestHandler::getInstance()->isACPRequest() ? 'acp/' : '').$url;
		}
		
		// append previously removed anchor
		$url .= $anchor;
		
		return $url;
	}
}
