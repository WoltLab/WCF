<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Route implementation to resolve HTTP requests.
 * 
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
 */
class Route implements IRoute {
	/**
	 * route controller if controller is no part of the route schema
	 * @var	string
	 */
	protected $controller = null;
	
	/**
	 * route is restricted to ACP
	 * @var	boolean
	 */
	protected $isACP = false;
	
	/**
	 * schema component options
	 * @var	array
	 */
	protected $parameterOptions = [];
	
	/**
	 * route name
	 * @var	string
	 */
	protected $routeName = '';
	
	/**
	 * route schema data
	 * @var	array
	 */
	protected $routeSchema = [];
	
	/**
	 * parsed route data
	 * @var	array
	 */
	protected $routeData = null;
	
	/**
	 * cached list of transformed controller names
	 * @var	string[]
	 */
	protected static $controllerNames = [];
	
	/**
	 * list of application abbreviation and default controller name
	 * @var	string[]
	 */
	protected static $defaultControllers = null;
	
	/**
	 * Creates a new route.
	 * 
	 * @param	string		$routeName
	 * @param	boolean		$isACP
	 */
	public function __construct($routeName, $isACP = false) {
		$this->isACP = $isACP;
		$this->routeName = $routeName;
	}
	
	/**
	 * Sets route schema, e.g. /{controller}/{id}.
	 * 
	 * @param	string		$routeSchema
	 * @param	string		$controller
	 * @throws	SystemException
	 */
	public function setSchema($routeSchema, $controller = null) {
		$schemaParts = $this->getParts($routeSchema);
		$hasController = false;
		$pattern = '~^{[a-zA-Z]+}$~';
		
		if ($controller !== null) {
			$this->controller = $controller;
			$hasController = true;
		}
		
		foreach ($schemaParts as &$part) {
			if (!preg_match($pattern, $part)) {
				throw new SystemException("Placeholder expected, but invalid string '" . $part . "' given.");
			}
			
			$part = str_replace(['{', '}'], '', $part);
			if ($part == 'controller') {
				if ($this->controller !== null) {
					throw new SystemException('Controller may not be part of the scheme if a route controller is given.');
				}
				
				$hasController = true;
			}
		}
		
		// each route must define a controller
		if (!$hasController) {
			throw new SystemException('Route schema does not provide a valid placeholder for controller.');
		}
		
		$this->routeSchema = $schemaParts;
	}
	
	/**
	 * Sets options for a route parameter.
	 * 
	 * @param	string		$key
	 * @param	string		$default
	 * @param	string		$regexPattern
	 * @param	boolean		$isOptional
	 */
	public function setParameterOption($key, $default = null, $regexPattern = null, $isOptional = false) {
		$this->parameterOptions[$key] = [
			'default' => $default,
			'isOptional' => $isOptional,
			'regexPattern' => $regexPattern
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function matches($requestURL) {
		$urlParts = $this->getParts($requestURL);
		$data = [];
		
		// handle each route schema component
		for ($i = 0, $size = count($this->routeSchema); $i < $size; $i++) {
			$schemaPart = $this->routeSchema[$i];
			
			if (isset($urlParts[$i])) {
				if (isset($this->parameterOptions[$schemaPart])) {
					// validate parameter against a regex pattern
					if ($this->parameterOptions[$schemaPart]['regexPattern'] !== null) {
						$pattern = '~^' . $this->parameterOptions[$schemaPart]['regexPattern'] . '$~';
						if (!URL_LEGACY_MODE && $schemaPart == 'controller') $pattern .= 'i';
						
						if (!preg_match($pattern, $urlParts[$i])) {
							return false;
						}
					}
				}
				
				// url component passed previous validation
				$data[$schemaPart] = $urlParts[$i];
			}
			else {
				if (isset($this->parameterOptions[$schemaPart])) {
					// default value is provided
					if ($this->parameterOptions[$schemaPart]['default'] !== null) {
						if ($schemaPart == 'controller') {
							$data['isDefaultController'] = true;
						}
						
						$data[$schemaPart] = $this->parameterOptions[$schemaPart]['default'];
						continue;
					}
					
					// required parameter is missing
					if (!$this->parameterOptions[$schemaPart]['isOptional']) {
						return false;
					}
				}
			}
		}
		
		if (!isset($data['isDefaultController'])) {
			$data['isDefaultController'] = false;
		}
		
		$this->routeData = $data;
		
		// adds route controller if given
		if ($this->controller !== null) {
			$this->routeData['controller'] = $this->controller;
		}
		
		if (!isset($this->routeData['controller'])) {
			$this->routeData['isDefaultController'] = true;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRouteData() {
		return $this->routeData;
	}
	
	/**
	 * Returns non-empty URL components.
	 * 
	 * @param	string		$requestURL
	 * @return	array
	 */
	protected function getParts($requestURL) {
		$urlParts = preg_split('~(\/|\-|\_|\.)~', $requestURL);
		foreach ($urlParts as $index => $part) {
			if (!mb_strlen($part)) {
				unset($urlParts[$index]);
			}
		}
		
		// re-index parts
		return array_values($urlParts);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canHandle(array $components) {
		foreach ($this->routeSchema as $schemaPart) {
			if (isset($components[$schemaPart])) {
				// validate parameter against a regex pattern
				if ($this->parameterOptions[$schemaPart]['regexPattern'] !== null) {
					$pattern = '~^' . $this->parameterOptions[$schemaPart]['regexPattern'] . '$~';
					if (!preg_match($pattern, $components[$schemaPart])) {
						return false;
					}
				}
			}
			else {
				if (isset($this->parameterOptions[$schemaPart])) {
					// default value is provided
					if ($this->parameterOptions[$schemaPart]['default'] !== null) {
						continue;
					}
					
					// required parameter is missing
					if (!$this->parameterOptions[$schemaPart]['isOptional']) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function buildLink(array $components) {
		$application = (isset($components['application'])) ? $components['application'] : null;
		self::loadDefaultControllers();
		
		// drop application component to avoid being appended as query string
		unset($components['application']);
		
		$link = '';
		
		// handle default values for controller
		$buildRoute = true;
		if (count($components) == 1 && isset($components['controller'])) {
			$ignoreController = false;
			if (isset($this->parameterOptions['controller']) && strcasecmp($this->parameterOptions['controller']['default'], $components['controller']) == 0) {
				// only the controller was given and matches default, omit routing
				$ignoreController = true;
			}
			else if (!RequestHandler::getInstance()->isACPRequest()) {
				/* TODO:
				$landingPage = PageMenu::getInstance()->getLandingPage();
				if ($landingPage !== null && strcasecmp($landingPage->getController(), $components['controller']) == 0) {
					$ignoreController = true;
				}*/
				
				// check if this is the default controller of the requested application
				/*
				 * TODO: what exactly is the check for the primary application doing?
				if (!URL_LEGACY_MODE && !$ignoreController && $application !== null) {
					if (isset(self::$defaultControllers[$application]) && self::$defaultControllers[$application] == $components['controller']) {
						// check if this is the primary application and the landing page originates to the same application
						$primaryApplication = ApplicationHandler::getInstance()->getPrimaryApplication();
						$abbreviation = ApplicationHandler::getInstance()->getAbbreviation($primaryApplication->packageID);
						if ($abbreviation != $application || $landingPage === null) {
							$ignoreController = true;
						}
					}
				}
				*/
			}
			
			// drops controller from route
			if ($ignoreController) {
				$buildRoute = false;
				
				// unset the controller, since it would otherwise be added with http_build_query()
				unset($components['controller']);
			}
		}
		
		if ($buildRoute) {
			foreach ($this->routeSchema as $component) {
				if (!isset($components[$component])) {
					continue;
				}
				
				// handle controller names
				if (!URL_LEGACY_MODE && $component === 'controller') {
					$components[$component] = $this->getControllerName($application, $components[$component]);
				}
				
				// handle built-in SEO
				if ($component === 'id' && isset($components['title'])) {
					$link .= $components[$component] . '-' . $components['title'] . '/';
					unset($components['title']);
				}
				else {
					$link .= $components[$component] . '/';
				}
				unset($components[$component]);
			}
		}
		
		// enforce non-legacy URLs for ACP and disregard rewrite settings
		if ($this->isACP()) {
			if (!empty($link)) {
				$link = '?' . $link;
			}
		}
		else if (!URL_OMIT_INDEX_PHP && !empty($link)) {
			$link = (URL_LEGACY_MODE ? 'index.php/' : '?') . $link;
		}
		
		if (!empty($components)) {
			if (strpos($link, '?') === false) $link .= '?';
			else $link .= '&';
			
			$link .= http_build_query($components, '', '&');
		}
		
		return $link;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isACP() {
		return $this->isACP;
	}
	
	/**
	 * Returns the transformed controller name.
	 *
	 * @param	string		$application
	 * @param	string		$controller
	 * @return	string
	 */
	protected function getControllerName($application, $controller) {
		if (!isset(self::$controllerNames[$controller])) {
			$controllerName = RequestHandler::getTokenizedController($controller);
			$alias = (!$this->isACP) ? RequestHandler::getInstance()->getAliasByController($controllerName) : null;
			
			self::$controllerNames[$controller] = ($alias) ?: $controllerName;
		}
		
		return self::$controllerNames[$controller];
	}
	
	/**
	 * Loads the default controllers for each active application.
	 */
	protected static function loadDefaultControllers() {
		if (self::$defaultControllers === null) {
			self::$defaultControllers = [];
			
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
				
				self::$defaultControllers[$controller[0]] = $controllerName;
			}
		}
	}
}
