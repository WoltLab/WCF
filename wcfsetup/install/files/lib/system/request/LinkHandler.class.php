<?php
namespace wcf\system\request;
use wcf\system\SingletonFactory;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\RouteHandler;

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
		
		// build route
		if ($controller !== null) {
			// handle object
			if (isset($parameters['object'])) {
				if (!($parameters['object'] instanceof \wcf\system\request\IRouteController) && $parameters['object'] instanceof \wcf\data\DatabaseObjectDecorator && $parameters['object']->getDecoratedObject() instanceof \wcf\system\request\IRouteController)  {
					$parameters['object'] = $parameters['object']->getDecoratedObject();
				}
				
				if ($parameters['object'] instanceof \wcf\system\request\IRouteController) {
					$parameters['id'] = $parameters['object']->getID();
					$parameters['title'] = $parameters['object']->getTitle();
				}
				
				unset($parameters['object']);
			}
			
			if (isset($parameters['title'])) {
				// remove illegal characters
				$parameters['title'] = trim(preg_replace('/[\x0-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+/', '-', $parameters['title']), '-');
			}
			
			$parameters['controller'] = $controller;
			$routeURL = RouteHandler::getInstance()->buildRoute($parameters);
			if (!$isRaw && !empty($url)) {
				$routeURL .= (strpos($routeURL, '?') === false) ? '?' : '&';
			}
			$url = $routeURL . $url;
		}
		
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
			
			$url = $application->domainName . $application->domainPath . $url;
		}
		
		return $url;
	}
}
