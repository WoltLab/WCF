<?php
namespace wcf\system\request;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;

/**
 * Handles routes for HTTP requests.
 * 
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
class RouteHandler extends SingletonFactory {
	/**
	 * router filter for ACP
	 * @var	boolean
	 */
	protected $isACP = false;
	
	/**
	 * list of available routes
	 * @var	array<wcf\system\request\Route>
	 */
	protected $routes = array();
	
	/**
	 * parsed route data
	 * @var	array
	 */
	protected $routeData = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->addDefaultRoutes();
		
		// fire event
		EventHandler::getInstance()->fireAction($this, 'didInit');
	}
	
	/**
	 * Adds default routes.
	 */
	protected function addDefaultRoutes() {
		$acpRoute = new Route('ACP_default', true);
		$acpRoute->setSchema('/{controller}/{id}');
		$acpRoute->setParameterOption('controller', 'Index', null, true);
		$acpRoute->setParameterOption('id', null, '\d+', true);
		$this->addRoute($acpRoute);
		
		$defaultRoute = new Route('default');
		$defaultRoute->setSchema('/{controller}/{id}');
		$defaultRoute->setParameterOption('controller', 'Index', null, true);
		$defaultRoute->setParameterOption('id', null, '\d+', true);
		$this->addRoute($defaultRoute);
	}
	
	/**
	 * Adds a new route to the beginning of all routes.
	 * 
	 * @param	wcf\system\request\Route	$route
	 */
	public function addRoute(Route $route) {
		array_unshift($this->routes, $route);
	}
	
	/**
	 * Returns true, if a route matches. Please bear in mind, that the
	 * first route which is able to consume all path components is used,
	 * even if other routes may fit better. Route order is crucial!
	 * 
	 * @param	boolean		$isACP
	 * @return	boolean
	 */
	public function matches($isACP) {
		$this->isACP = $isACP;
		
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '';
		
		foreach ($this->routes as $route) {
			if ($this->isACP != $route->isACP()) {
				continue;
			}
			
			if ($route->matches($pathInfo)) {
				$this->routeData = $route->getRouteData();
				$this->registerRouteData();
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns parsed route data
	 * 
	 * @return	array
	 */
	public function getRouteData() {
		return $this->routeData;
	}
	
	/**
	 * Registers route data within $_GET and $_REQUEST.
	 */	
	protected function registerRouteData() {
		foreach ($this->routeData as $key => $value) {
			$_GET[$key] = $value;
			$_REQUEST[$key] = $value;
		}
	}
	
	/**
	 * Builds a route based upon route components, this is nothing
	 * but a reverse lookup.
	 * 
	 * @param	array		$components
	 * @return	string
	 */
	public function buildRoute(array $components) {
		foreach ($this->routes as $route) {
			if ($this->isACP != $route->isACP()) {
				continue;
			}
			
			if ($route->canHandle($components)) {
				return $route->buildLink($components);
			}
		}
		
		throw new SystemException("Unable to build route, no available route is satisfied.");
	}
}
