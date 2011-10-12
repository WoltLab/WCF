<?php
namespace wcf\system\request;
use wcf\system\exception\SystemException;

/**
 * Route implementation to resolve HTTP requests.
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
class Route {
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
	 * Sets route schema, e.g. /{controller}/{id}
	 * 
	 * @param	string		$routeSchema
	 */
	public function setSchema($routeSchema) {
		$schemaParts = $this->getParts($routeSchema);
		$hasController = false;
		$pattern = '~^{[a-zA-Z]+}$~';
		foreach ($schemaParts as &$part) {
			if (!preg_match($pattern, $part)) {
				throw new SystemException("Placeholder expected, but invalid string '" . $part . "' given.");
			}
			
			$part = str_replace(array('{', '}'), '', $part);
			if ($part == 'controller') {
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
		if ($key == 'controller' && (empty($default) && $isOptional)) {
			throw new SystemException('Routes require a controller, it is not possible to regard them as optional without a default value.');
		}
		
		$this->parameterOptions[$key] = array(
			'default' => $default,
			'isOptional' => $isOptional,
			'regexPattern' => $regexPattern
		);
	}
	
	/**
	 * Returns true, if given request url matches this route.
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
		
		$this->routeData = $data;
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
		$link = 'index.php/';
		foreach ($this->routeSchema as $component) {
			if (!isset($components[$component])) {
				continue;
			}
			
			$link .= $components[$component] . '/';
			unset($components[$component]);
		}
		
		if (!empty($components)) {
			$link .= '?' . html_build_query($components, '', '&');
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
