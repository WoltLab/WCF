<?php
namespace wcf\system\route;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\StringUtil;
 
/**
 * Represents a route.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.route
 * @category 	Community Framework
 */
class Route {
	/**
	 * components of this route
	 * @var	array<wcf\system\route\RouteComponent>
	 */
	protected $components = array();
	
	/**
	 * route controller if controller is no part of the route schema
	 * @var	string
	 */
	protected $controller = null;
	
	/**
	 * indicates if this route is for the acp
	 * @var	boolean
	 */
	protected $isACPRoute = false;
	
	/**
	 * pattern for cutting a request url/a route schema in pieces
	 * @var	string
	 */
	protected $partsPattern = null;
	
	/**
	 * route data sorted by the hashed request urls
	 * @var	array<array>
	 */
	protected $routeDataByURL = array();
	
	/**
	 * route name
	 * @var	string
	 */
	protected $routeName = '';
	
	/**
	 * route schema
	 * @var	string
	 */
	protected $routeSchema = '';
	
	/**
	 * parts of the route schema
	 * @var	array<string>
	 */
	protected $routeSchemaParts = array();
	
	/**
	 * pattern used to prepare strings (removing illeagl characters)
	 * @var	string
	 */
	protected $stringPreparationPattern = null;
	
	/**
	 * default pattern used to cut a request url/a route schema in pieces
	 * @var	string
	 */
	protected static $defaultPartsPattern = '(/|\-|\.)';
	
	/**
	 * default pattern used to prepare strings (removing illeagl characters)
	 * @var	string
	 */
	protected static $defaultStringPreparationPattern = '[\x0-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	
	/**
	 * Creates a new route.
	 * 
	 * @param	string		$routeName
	 * @param	boolean		$isACPRoute
	 */
	public function __construct($routeName, $routeSchema, $controller = null, $isACPRoute = false, $partsPattern = null, $stringPreparationPattern = null) {
		$this->isACPRoute = $isACPRoute;
		$this->routeName = $routeName;
		$this->controller = $controller;
		$this->partsPattern = $partsPattern;
		$this->routeSchema = $routeSchema;
		$this->stringPreparationPattern = $stringPreparationPattern;
		
		$this->validateSchema();
	}
	
	/**
	 * Validates the route schema.
	 */
	protected function validateSchema() {
		$this->routeSchemaParts = $this->getParts($this->routeSchema);
		$hasController = $this->controller !== null;
		
		foreach ($this->routeSchemaParts as &$part) {
			if ($part == '{controller}') {
				if ($hasController) {
					throw new SystemException('Controller may not be part of the scheme if a route controller is given.');
				}
				
				$hasController = true;
			}
		}
		
		// each route must define a controller
		if (!$hasController) {
			throw new SystemException('Route schema does not provide a valid placeholder for controller.');
		}
	}
	
	/**
	 * Adds given route components to this route.
	 * 
	 * @param	array<wcf\system\route\RouteComponent>	$components
	 */
	public function addComponents(array $components) {
		foreach ($components as $component) {
			if ($component instanceof RouteComponent === false) {
				throw new SystemException("A route component has to be a RouteComponent object.");
			}
			if (isset($this->components[$component->name])) {
				throw new SystemException("Route component '".$component->name."' already exists.");	
			}
			
			$this->components[$component->name] = $component;
		}
	}
	
	/**
	 * Returns true, if given request url matches this route.
	 * 
	 * @param	string		$requestURL
	 * @return	boolean
	 */
	public function matches($requestURL) {
		if (isset($this->routeDatabyURL[StringUtil::getHash($requestURL)])) {
			return true;
		}
		
		$urlParts = $this->getParts($requestURL);
		$data = array();
		
		// handle each route schema component
		for ($i = 0, $size = count($this->routeSchemaParts); $i < $size; $i++) {
			$schemaPart = StringUtil::replace(array('{', '}'), '', $this->routeSchemaParts[$i]);
			
			// check if part is static
			if ($schemaPart == $this->routeSchemaParts[$i]) {
				// check if static part exists in the url
				if (array_search($schemaPart, $urlParts) !== false) {
					continue;
				}
				
				return false;
			}
			
			$component = $this->components[$schemaPart];
			
			if (isset($urlParts[$i])) {
				// check if url part matches component
				if ($component !== null) {
					if (!$component->matches($urlParts[$i])) {
						return false;
					}
				}
				
				// url component passed previous validation
				$data[$schemaPart] = $urlParts[$i];
			}
			else {
				if ($component !== null) {
					// default value is provided
					if ($component->defaultValue !== null) {
						$data[$schemaPart] = $component->defaultValue;
						continue;
					}
					
					// required parameter is missing
					if (!$component->isOptional) {
						return false;
					}
				}
			}
		}
		
		$this->routeDataByURL[StringUtil::getHash($requestURL)] = $data;
		
		// adds route controller if given
		if ($this->controller !== null) {
			$this->routeDataByURL[StringUtil::getHash($requestURL)]['controller'] = $this->controller;
		}
		
		return true;
	}
	
	/**
	 * Returns parsed route data for the given url.
	 * 
	 * @return	string		$requestURL
	 * @return	array
	 */
	public function getRouteData($requestURL) {
		if (!isset($this->routeDataByURL[StringUtil::getHash($requestURL)])) {
			throw new SystemException("No route data for requets url '".$requestURL."'");
		}
		
		return $this->routeDataByURL[StringUtil::getHash($requestURL)];
	}
	
	/**
	 * Returns true if current route can handle the build request.
	 * 
	 * @param	array		$components
	 * @return	boolean
	 */
	public function canHandle(array $components) {
		foreach ($this->components as $component) {
			if (isset($components[$component->name])) {
				if (!$component->matches($this->prepareString($components[$component->name]))) {
					return false;
				}
			}
			else {
				// check if component has default value
				if ($component->defaultValue !== null) {
					continue;
				}
				
				// check if component is option
				if (!$component->isOptional) {
					return false;
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
		$link = $this->routeSchema;
		foreach ($this->routeSchemaParts as $realPart) {
			$part = StringUtil::replace(array('{', '}'), '', $realPart);
			
			// check if part is static
			if ($part == $realPart) {
				continue;
			}
			
			if ($part == 'controller' && $this->controller) {
				$components[$part] = $this->controller;
			}
			
			if (!isset($components[$part])) {
				// check if missing component is optional
				if ($this->components[$part] !== null && $this->components[$part]->isOptional) {
					// cosmetic corrections: remove part delimiters for missing optional components
					$regex = new Regex(static::$defaultPartsPattern.'{'.$part.'}'.static::$defaultPartsPattern.'?');
					if ($regex->match($link)) {
						$matches = $regex->getMatches();
						$replace = '/';
						if (!isset($matches[2])) {
							$replace = $matches[2] = '';
						}
						if ($matches[1] != '/' && $matches[2] != '/') {
							$replace = $matches[2];
						}

						$link = StringUtil::replace($matches[1].'{'.$part.'}'.$matches[2], $replace, $link);
						continue;
					}
 
					$components[$part] = '';
				}
				else {
					throw new SystemException("Missing route component '".$part."'");
				}
			}
			
			$link = StringUtil::replace('{'.$part.'}', $this->prepareString($components[$part]), $link);
			unset($components[$part]);
		}
		
		// unset controller component if route schema doesn't contain an
		// explicit controller
		if (isset($components['controller'])) {
			unset($components['controller']);
		}
		
		$link = 'index.php' . (!empty($link) && $link[0] != '/' ? '/' : '') . $link . (StringUtil::substring($link, -1) != '/' ? '/' : '');
		
		if (!empty($components)) {
			$link .= '?' . http_build_query($components, '', '&');
		}
		
		return $link;
	}
	
	/**
	 * Returns non-empty URL components.
	 * 
	 * @param	string		$requestURL
	 * @return	array
	 */
	protected function getParts($requestURL) {
		$urlParts = preg_split('~'.($this->partsPattern ?: static::$defaultPartsPattern).'~', $requestURL);
		foreach ($urlParts as $index => $part) {
			if (empty($part)) {
				unset($urlParts[$index]);
			}
		}
		
		// re-index parts
		return array_values($urlParts);
	}
	
	/**
	 * Returns true, if this is a route for the acp.
	 * 
	 * @return	boolean
	 */
	public function isACPRoute() {
		return $this->isACPRoute;
	}
	
	/**
	 * Prepares a string to be used in a link by removing illegal characters.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function prepareString($string) {
		return trim(Regex::compile($this->stringPreparationPattern ?: static::$defaultStringPreparationPattern)->replace($string, '_'), '_');
	}
}
