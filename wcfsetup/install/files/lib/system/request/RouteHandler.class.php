<?php
namespace wcf\system\request;
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
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
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
	 * HTTP protocol, either 'http://' or 'https://'
	 * @var	string
	 */
	protected static $protocol = '';
	
	/**
	 * HTTP encryption
	 * @var	boolean
	 */
	protected static $secure = null;
	
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
		
		if (MODULE_API_ACCESS) {
			$apiRoute = new Route('api');
			$apiRoute->setSchema('/{controller}/{className}-{id}');
			$apiRoute->setParameterOption('controller', 'API');
			$apiRoute->setParameterOption('className', null, '\w+');
			$apiRoute->setParameterOption('id', null, '\d+');
			$this->addRoute($apiRoute);
		}
		
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
	 * @return	boolean
	 */
	public function matches() {
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '';
		
		foreach ($this->routes as $route) {
			if (RequestHandler::getInstance()->isACPRequest() != $route->isACP()) {
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
			if (RequestHandler::getInstance()->isACPRequest() != $route->isACP()) {
				continue;
			}
			
			if ($route->canHandle($components)) {
				return $route->buildLink($components);
			}
		}
		
		throw new SystemException("Unable to build route, no available route is satisfied.");
	}
	
	/**
	 * Returns true, if this is a secure connection.
	 * 
	 * @return	true
	 */
	public static function secureConnection() {
		if (self::$secure === null) {
			self::$secure = false;
			
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443) {
				self::$secure = true;
			}
		}
		
		return self::$secure;
	}
	
	/**
	 * Returns HTTP protocol, either 'http://' or 'https://'.
	 * 
	 * @return	string
	 */
	public static function getProtocol() {
		if (empty(self::$protocol)) {
			self::$protocol = 'http' . (self::secureConnection() ? 's' : '') . '://';
		}
		
		return self::$protocol;
	}
	
	/**
	 * Returns protocol and domain name.
	 * 
	 * @return	string
	 */
	public static function getHost() {
		if (empty(self::$host)) {
			self::$host = self::getProtocol() . $_SERVER['HTTP_HOST'];
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
