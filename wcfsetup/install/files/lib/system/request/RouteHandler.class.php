<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\route\DynamicRequestRoute;
use wcf\system\request\route\LookupRequestRoute;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Handles routes for HTTP requests.
 * 
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
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
	 * current path info component
	 * @var	string
	 */
	protected static $pathInfo = null;
	
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
	 * list of application abbreviation and default controller name
	 * @var	string[]
	 */
	protected $defaultControllers = null;
	
	/**
	 * true, if default controller is used (support for custom landing page)
	 * @var	boolean
	 */
	protected $isDefaultController = false;
	
	/**
	 * list of available routes
	 * @var	IRoute[]
	 */
	protected $routes = [];
	
	/**
	 * parsed route data
	 * @var	array
	 */
	protected $routeData = null;
	
	/**
	 * Sets default routes.
	 */
	protected function init() {
		$route = new DynamicRequestRoute();
		$route->setIsACP(true);
		$this->addRoute($route);
		
		$route = new DynamicRequestRoute();
		$this->addRoute($route);
		
		$route = new LookupRequestRoute();
		$this->addRoute($route);
		
		// fire event
		EventHandler::getInstance()->fireAction($this, 'didInit');
	}
	
	/**
	 * Adds a new route to the beginning of all routes.
	 * 
	 * @param	IRoute  $route
	 */
	public function addRoute(IRoute $route) {
		array_unshift($this->routes, $route);
	}
	
	/**
	 * Returns all registered routes. 
	 * 
	 * @return	IRoute[]
	 **/
	public function getRoutes() {
		return $this->routes; 
	}
	
	/**
	 * Returns true if a route matches. Please bear in mind, that the
	 * first route that is able to consume all path components is used,
	 * even if other routes may fit better. Route order is crucial!
	 * 
	 * @return	boolean
	 */
	public function matches() {
		foreach ($this->routes as $route) {
			if (RequestHandler::getInstance()->isACPRequest() != $route->isACP()) {
				continue;
			}
			
			if ($route->matches(self::getPathInfo())) {
				$this->routeData = $route->getRouteData();
				
				$this->isDefaultController = $this->routeData['isDefaultController'];
				unset($this->routeData['isDefaultController']);
				
				$this->registerRouteData();
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns true if route uses default controller.
	 * 
	 * @return	boolean
	 */
	public function isDefaultController() {
		return $this->isDefaultController;
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
	 * @param	string		$application	application identifier
	 * @param	array		$components
	 * @param	boolean		$isACP
	 * @return	string
	 * @throws	SystemException
	 */
	public function buildRoute($application, array $components, $isACP = null) {
		if ($isACP === null) $isACP = RequestHandler::getInstance()->isACPRequest();
		$components['application'] = $application;
		
		foreach ($this->routes as $route) {
			if ($isACP != $route->isACP()) {
				continue;
			}
			
			if ($route->canHandle($components)) {
				return $route->buildLink($components);
			}
		}
		
		throw new SystemException("Unable to build route, no available route is satisfied.");
	}
	
	/**
	 * Returns true if `$customUrl` contains only the letters a-z/A-Z, numbers, dashes,
	 * underscores and forward slashes.
	 * 
	 * All other characters including those from the unicode range are potentially unsafe,
	 * especially when dealing with url rewriting and resulting encoding issues with some
	 * webservers.
	 * 
	 * This heavily limits the abilities for end-users to define appealing urls, but at
	 * the same time this ensures a sufficient level of stability.
	 * 
	 * @param	string	$customUrl	url to perform sanitiy checks on
	 * @return	bool	true if `$customUrl` passes the sanity check
	 * @since	3.0
	 */
	public static function isValidCustomUrl($customUrl) {
		return preg_match('~^[a-zA-Z0-9\-_/]+$~', $customUrl) === 1;
	}
	
	/**
	 * Returns true if this is a secure connection.
	 * 
	 * @return	true
	 */
	public static function secureConnection() {
		if (self::$secure === null) {
			self::$secure = false;
			
			if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
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
	public static function getPath(array $removeComponents = []) {
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
			
			return FileUtil::addTrailingSlash('/' . implode('/', $path));
		}
		
		return self::$path;
	}
	
	/**
	 * Returns current path info component.
	 * 
	 * @return	string
	 */
	public static function getPathInfo() {
		if (self::$pathInfo === null) {
			self::$pathInfo = '';
			
			if (!URL_LEGACY_MODE || RequestHandler::getInstance()->isACPRequest()) {
				// WCF 2.1: ?Foo/Bar/
				if (!empty($_SERVER['QUERY_STRING'])) {
					// don't use parse_str as it replaces dots with underscores
					$components = explode('&', $_SERVER['QUERY_STRING']);
					for ($i = 0, $length = count($components); $i < $length; $i++) {
						$component = $components[$i];
						
						$pos = mb_strpos($component, '=');
						if ($pos !== false && $pos + 1 === mb_strlen($component)) {
							$component = mb_substr($component, 0, -1);
							$pos = false;
						}
						
						if ($pos === false) {
							self::$pathInfo = urldecode($component);
							break;
						}
					}
				}
			}
			
			// WCF 2.0: index.php/Foo/Bar/
			if ((URL_LEGACY_MODE && !RequestHandler::getInstance()->isACPRequest()) || (RequestHandler::getInstance()->isACPRequest() && empty(self::$pathInfo))) {
				if (isset($_SERVER['PATH_INFO'])) {
					self::$pathInfo = $_SERVER['PATH_INFO'];
				}
				else if (isset($_SERVER['ORIG_PATH_INFO'])) {
					self::$pathInfo = $_SERVER['ORIG_PATH_INFO'];
						
					// in some configurations ORIG_PATH_INFO contains the path to the file
					// if the intended PATH_INFO component is empty
					if (!empty(self::$pathInfo)) {
						if (isset($_SERVER['SCRIPT_NAME']) && (self::$pathInfo == $_SERVER['SCRIPT_NAME'])) {
							self::$pathInfo = '';
						}
						
						if (isset($_SERVER['PHP_SELF']) && (self::$pathInfo == $_SERVER['PHP_SELF'])) {
							self::$pathInfo = '';
						}
						
						if (isset($_SERVER['SCRIPT_URL']) && (self::$pathInfo == $_SERVER['SCRIPT_URL'])) {
							self::$pathInfo = '';
						}
					}
				}
			}
		}
		
		return self::$pathInfo;
	}
	
	/**
	 * Returns the default controller name for given application.
	 * 
	 * @param	string		$application
	 * @return	string
	 */
	public function getDefaultController($application) {
		$this->loadDefaultControllers();
		
		if (isset($this->defaultControllers[$application])) {
			return $this->defaultControllers[$application];
		}
		
		return '';
	}
	
	/**
	 * Loads the default controllers for each active application.
	 */
	protected function loadDefaultControllers() {
		if ($this->defaultControllers === null) {
			$this->defaultControllers = [];
			
			foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
				$app = WCF::getApplicationObject($application);
				
				if (!$app) {
					continue;
				}
				
				$controller = $app->getPrimaryController();
				
				if (!$controller) {
					continue;
				}
				
				$controller = explode('\\', $controller);
				$controllerName = preg_replace('~(Action|Form|Page)$~', '', array_pop($controller));
				
				$this->defaultControllers[$controller[0]] = $controllerName;
			}
		}
	}
}
