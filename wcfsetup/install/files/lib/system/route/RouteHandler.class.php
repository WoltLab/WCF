<?php
namespace wcf\system\route;
use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\FileUtil;

/**
 * Handles routes for HTTP requests.
 * 
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 * 
 * @author 	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.route
 * @category 	Community Framework
 */
class RouteHandler extends SingletonFactory {
	/**
	 * current host and protocol
	 * @var	string
	 */
	protected static $host = '';
	
	/**
	 * current absolute path
	 * @var	string
	 */
	protected static $path = '';
	
	/**
	 * router filter for ACP
	 * @var	boolean
	 */
	protected $isACP = false;
	
	/**
	 * list of available routes
	 * @var	array<wcf\data\route\Route>
	 */
	protected $routes = array();
	
	/**
	 * list of available route components
	 * @var	array<wcf\data\route\component\RouteComponent>
	 */
	protected $components = array();
	
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
		$defaultRouteComponents = array(
			new RouteComponent('controller', 'Index', null, true),
			new RouteComponent('id', null, '\d+', true),
			new RouteComponent('title', null, '\w+', true)
		);
		
		$acpRoute = new Route('com.woltlab.wcf.acp.default', '/{controller}/{id}-{title}', null, true);
		$acpRoute->addComponents($defaultRouteComponents);
		$this->addRoute($acpRoute);
		
		$route = new Route('com.woltlab.wcf.default', '/{controller}/{id}-{title}');
		$route->addComponents($defaultRouteComponents);
		$this->addRoute($route);
	}
	
	/**
	 * Adds a new route to the beginning of all routes.
	 * 
	 * @param	wcf\system\route\Route	$route
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
			if ($this->isACP != $route->isACPRoute()) {
				continue;
			}
			
			if ($route->matches($pathInfo)) {
				$this->routeData = $route->getRouteData($pathInfo);
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
			if ($this->isACP != $route->isACPRoute()) {
				continue;
			}
			
			if ($route->canHandle($components)) {
				return $route->buildLink($components);
			}
		}
		
		throw new SystemException("Unable to build route, no available route is satisfied.");
	}
	
	/**
	 * Returns protocol and domain name.
	 * 
	 * @return	string
	 */
	public static function getHost() {
		if (empty(self::$host)) {
			// get protocol and domain name
			$protocol = 'http://';
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443) {
				$protocol = 'https://';
			}
			
			self::$host = $protocol . $_SERVER['HTTP_HOST'];
		}
		
		return self::$host;
	}
	
	/**
	 * Returns absolute domain path.
	 * 
	 * @param	array		$removeComponents
	 * @return	string
	 */
	public static function getPath(array $removeComponents = array()) {
		if (empty(self::$path)) {
			self::$path = FileUtil::addTrailingSlash(dirname($_SERVER['SCRIPT_NAME']));
		}
		
		if (!empty($removeComponents)) {
			$path = explode('/', self::$path);
			foreach ($path as $index => $component) {
				if (empty($path[$index])) {
					unset($path[$index]);
				}
				
				if (in_array($component, $removeComponents)) {
					unset($path[$index]);
				}
			}
			
			return '/' . implode('/', $path) . '/';
		}
		
		return self::$path;
	}
}
