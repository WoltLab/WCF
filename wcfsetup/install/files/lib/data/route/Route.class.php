<?php
namespace wcf\data\route;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\route\RouteHandler;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Represents a route.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.route
 * @category 	Community Framework
 */
class Route extends DatabaseObject {
	/**
	 * components of this route
	 * @var	array<wcf\data\route\component\RouteComponent>
	 */
	protected $components = null;
	
	/**
	 * route data sorted by the hashed request urls
	 * @var	array<array>
	 */
	protected $routeDataByURL = array();
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'route';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'routeID';
	
	/**
	 * default pattern for cutting a request url a route schema in pieces
	 * @var	string
	 */
	protected static $defaultPartsPattern = '(\/|\-|\_|\.)';
	
	/**
	 * Returns the object of this route's component with the given name or null
	 * if no such component exists.
	 * 
	 * @param	string		$componentName
	 * @return	wcf\data\route\component\RouteComponent
	 */
	public function getComponentByName($componentName) {
		$this->loadComponents();
		foreach ($this->components as $component) {
			if ($component->componentName == $componentName) {
				return $component;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns all component objects of this route.
	 * 
	 * @return	array<wcf\data\route\component\RouteComponent>
	 */
	public function getComponents() {
		$this->loadComponents();
		
		return $this->components;
	}
	
	/**
	 * Loads the components of this route.
	 */
	protected function loadComponents() {
		if ($this->components === null) {
			$this->components = RouteHandler::getInstance()->getComponents($this);
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
		$schemaParts = $this->getParts($this->routeSchema);
		$data = array();
		
		// handle each route schema component
		for ($i = 0, $size = count($schemaParts); $i < $size; $i++) {
			$schemaPart = StringUtil::replace(array('{', '}'), '', $schemaParts[$i]);
			
			$component = $this->getComponentByName($schemaPart);
			if (isset($urlParts[$i])) {
				if ($component !== null) {
					// validate parameter against a regex pattern
					if ($component->pattern !== null) {
						if (!Regex::compile('^'.$component->pattern.'$')->match($urlParts[$i])) {
							return false;
						}
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
		foreach ($this->getComponents() as $component) {
			if (isset($components[$component->componentName])) {
				if ($component->pattern !== null) {
					if (!Regex::compile('^'.$component->pattern.'$')->match($components[$component->componentName])) {	
						return false;
					}
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
		foreach ($this->getParts($this->routeSchema) as $part) {
			$part = StringUtil::replace(array('{', '}'), '', $part);

			if ($part == 'controller' && $this->controller) {
				$components[$part] = $this->controller;
			}

			if (!isset($components[$part])) {
				// check if missing component is optional
				if ($this->getComponentByName($part) !== null && $this->getComponentByName($part)->isOptional) {
					// avoid double slashes
					if (StringUtil::indexOf($link, '/{'.$part.'}/')) {
						$link = StringUtil::replace('/{'.$part.'}/', '/', $link);
						continue;
					}

					$components[$part] = '';
				}
				else {
					throw new SystemException("Missing route component '".$part."'");
				}
			}

			$link = StringUtil::replace('{'.$part.'}', $components[$part], $link);
			unset($components[$part]);
		}
		
		$link = 'index.php' . (!empty($link) && $link[0] != '/' ? '/' : '') . $link;
		
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
}
