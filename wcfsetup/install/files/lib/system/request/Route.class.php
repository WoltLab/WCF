<?php
namespace wcf\system\request;
use wcf\system\exception\SystemException;
use wcf\system\menu\page\PageMenu;

/**
 * Route implementation to resolve HTTP requests.
 * 
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class Route {
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
	protected $parameterOptions = array();
	
	/**
	 * route name
	 * @var	string
	 */
	protected $routeName = '';
	
	/**
	 * route schema data
	 * @var	array
	 */
	protected $routeSchema = array();
	
	/**
	 * parsed route data
	 * @var	array
	 */
	protected $routeData = null;
	
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
			
			$part = str_replace(array('{', '}'), '', $part);
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
		$this->parameterOptions[$key] = array(
			'default' => $default,
			'isOptional' => $isOptional,
			'regexPattern' => $regexPattern
		);
	}
	
	/**
	 * Returns true if given request url matches this route.
	 * 
	 * @param	string		$requestURL
	 * @return	boolean
	 */
	public function matches($requestURL) {
		$urlParts = $this->getParts($requestURL);
		$data = array();
		
		// handle each route schema component
		for ($i = 0, $size = count($this->routeSchema); $i < $size; $i++) {
			$schemaPart = $this->routeSchema[$i];
			
			if (isset($urlParts[$i])) {
				if (isset($this->parameterOptions[$schemaPart])) {
					// validate parameter against a regex pattern
					if ($this->parameterOptions[$schemaPart]['regexPattern'] !== null) {
						$pattern = '~^' . $this->parameterOptions[$schemaPart]['regexPattern'] . '$~';
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
	 * Returns parsed route data.
	 * 
	 * @return	array
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
			if (empty($part)) {
				unset($urlParts[$index]);
			}
		}
		
		// re-index parts
		return array_values($urlParts);
	}
	
	/**
	 * Returns true if current route can handle the build request.
	 * 
	 * @param	array		$components
	 * @return	boolean
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
	 * Builds a link upon route components.
	 * 
	 * @param	array		$components
	 * @return	string
	 */
	public function buildLink(array $components) {
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
				$landingPage = PageMenu::getInstance()->getLandingPage();
				if ($landingPage !== null && ($landingPage->getController() == $components['controller'])) {
					$ignoreController = true;
				}
			}
			
			// drops controller from route
			if ($ignoreController) {
				$buildRoute = false;
				
				// unset the controller, since it would otherwise added with http_build_query()
				unset($components['controller']);
			}
		}
		
		if ($buildRoute) {
			foreach ($this->routeSchema as $component) {
				if (!isset($components[$component])) {
					continue;
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
		
		if (!empty($link)) {
			$link = 'index.php/' . $link;
		}
		
		if (!empty($components)) {
			$link .= '?' . http_build_query($components, '', '&');
		}
		
		return $link;
	}
	
	/**
	 * Returns true if route applies for ACP.
	 * 
	 * @return	boolean
	 */
	public function isACP() {
		return $this->isACP;
	}
}
