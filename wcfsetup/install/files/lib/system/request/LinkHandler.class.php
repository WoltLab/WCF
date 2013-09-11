<?php
namespace wcf\system\request;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\application\ApplicationHandler;
use wcf\system\menu\page\PageMenu;
use wcf\system\request\RouteHandler;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Handles relative links within the wcf.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class LinkHandler extends SingletonFactory {
	/**
	 * regex object to filter title
	 * @var	wcf\system\RegEx
	 */
	protected $titleRegex = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->titleRegex = new Regex('[\x0-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+');
	}
	
	/**
	 * Returns a relative link.
	 * 
	 * @param	string		$controller
	 * @param	array		$parameters
	 * @param	string		$url
	 * @return	string
	 */
	public function getLink($controller = null, array $parameters = array(), $url = '') {
		$abbreviation = 'wcf';
		$anchor = '';
		$isACP = $originIsACP = RequestHandler::getInstance()->isACPRequest();
		$encodeTitle = $forceWCF = $isRaw = false;
		$appendSession = true;
		if (isset($parameters['application'])) {
			$abbreviation = $parameters['application'];
			unset($parameters['application']);
		}
		if (isset($parameters['isRaw'])) {
			$isRaw = $parameters['isRaw'];
			unset($parameters['isRaw']);
		}
		if (isset($parameters['appendSession'])) {
			$appendSession = $parameters['appendSession'];
			unset($parameters['appendSession']);
		}
		if (isset($parameters['isACP'])) {
			$isACP = (bool) $parameters['isACP'];
			unset($parameters['isACP']);
			
			// drop session id if link leads to ACP from frontend or vice versa
			if ($originIsACP != $isACP) {
				$appendSession = false;
			}
		}
		if (isset($parameters['forceFrontend'])) {
			if ($parameters['forceFrontend'] && $isACP) {
				$isACP = false;
				$appendSession = false;
			}
			unset($parameters['forceFrontend']);
		}
		if (isset($parameters['forceWCF'])) {
			if ($parameters['forceWCF'] && $isACP) {
				$forceWCF = true;
			}
			unset($parameters['forceWCF']);
		}
		if (isset($parameters['encodeTitle'])) {
			$encodeTitle = $parameters['encodeTitle'];
			unset($parameters['encodeTitle']);
		}
		
		// remove anchor before parsing
		if (($pos = strpos($url, '#')) !== false) {
			$anchor = substr($url, $pos);
			$url = substr($url, 0, $pos);
		}
		
		// build route
		if ($controller === null) {
			if ($isACP) {
				$controller = 'Index';
			}
			else {
				// build link to landing page
				$landingPage = PageMenu::getInstance()->getLandingPage();
				$controller = $landingPage->getController();
				$abbreviation = $landingPage->getApplication();
				$url = $landingPage->menuItemLink;
			}
		}
		
		// handle object
		if (isset($parameters['object'])) {
			if (!($parameters['object'] instanceof IRouteController) && $parameters['object'] instanceof DatabaseObjectDecorator && $parameters['object']->getDecoratedObject() instanceof IRouteController) {
				$parameters['object'] = $parameters['object']->getDecoratedObject();
			}
			
			if ($parameters['object'] instanceof IRouteController) {
				$parameters['id'] = $parameters['object']->getObjectID();
				$parameters['title'] = $parameters['object']->getTitle();
			}
		}
		unset($parameters['object']);
		
		if (isset($parameters['title'])) {
			// remove illegal characters
			$parameters['title'] = trim($this->titleRegex->replace($parameters['title'], '-'), '-');
			
			// trim to 80 characters
			$parameters['title'] = mb_substr($parameters['title'], 0, 80);
			
			// encode title
			if ($encodeTitle) $parameters['title'] = rawurlencode($parameters['title']);
		}
		
		$parameters['controller'] = $controller;
		$routeURL = RouteHandler::getInstance()->buildRoute($parameters, $isACP);
		if (!$isRaw && !empty($url)) {
			$routeURL .= (strpos($routeURL, '?') === false) ? '?' : '&';
		}
		
		// encode certain characters
		if (!empty($url)) {
			$url = str_replace(array('[', ']'), array('%5B', '%5D'), $url);
		}
		
		$url = $routeURL . $url;
		
		// append session id
		if ($appendSession) {
			$url .= (strpos($url, '?') === false) ? SID_ARG_1ST : SID_ARG_2ND_NOT_ENCODED;
		}
		
		// handle applications
		if (!PACKAGE_ID) {
			$url = RouteHandler::getHost() . RouteHandler::getPath(array('acp')) . ($isACP ? 'acp/' : '') . $url;
		}
		else {
			if (RequestHandler::getInstance()->inRescueMode()) {
				$pageURL = RouteHandler::getHost() . str_replace('//', '/', RouteHandler::getPath(array('acp')));
			}
			else {
				// try to resolve abbreviation
				$application = null;
				if ($abbreviation != 'wcf') {
					$application = ApplicationHandler::getInstance()->getApplication($abbreviation);
				}
				
				// fallback to primary application if abbreviation is 'wcf' or unknown
				if ($forceWCF) {
					$application = ApplicationHandler::getInstance()->getWCF();
				}
				else if ($application === null) {
					$application = ApplicationHandler::getInstance()->getPrimaryApplication();
				}
				
				$pageURL = $application->getPageURL();
			}
			
			$url = $pageURL . ($isACP ? 'acp/' : '') . $url;
		}
		
		// append previously removed anchor
		$url .= $anchor;
		
		return $url;
	}
}
