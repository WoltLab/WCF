<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\menu\page\PageMenu;

/**
 * Flexible route implementation to resolve HTTP requests.
 * 
 * Inspired by routing mechanism used by ASP.NET MVC and released under the terms of
 * the Microsoft Public License (MS-PL) http://www.opensource.org/licenses/ms-pl.html
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class FlexibleRoute implements IRoute {
	/**
	 * schema for outgoing links
	 * @var	array<array>
	 */
	protected $buildSchema = array();
	
	/**
	 * cached list of transformed controller names
	 * @var	array<string>
	 */
	protected $controllerNames = array();
	
	/**
	 * route is restricted to ACP
	 * @var	boolean
	 */
	protected $isACP = false;
	
	/**
	 * pattern for incoming requests
	 * @var	string
	 */
	protected $pattern = '';
	
	/**
	 * list of required components
	 * @var	array<string>
	 */
	protected $requireComponents = array();
	
	/**
	 * parsed request data
	 * @var	array<mixed>
	 */
	protected $routeData = array();
	
	/**
	 * Creates a new flexible route instace.
	 * 
	 * @param	boolean		$isACP
	 */
	public function __construct($isACP) {
		$this->isACP = $isACP;
		
		$this->pattern = '~
			/?
			(?:
				(?P<controller>[A-Za-z0-9\-]+)
				(?:
					/
					(?P<id>\d+)
					(?:
						-
						(?P<title>[^/]+)
					)?
				)?
			)?
		~x';
		$this->setBuildSchema('/{controller}/{id}-{title}/');
	}
	
	/**
	 * Sets the build schema used to build outgoing links.
	 * 
	 * @param	string		$buildSchema
	 */
	public function setBuildSchema($buildSchema) {
		$this->buildSchema = array();
		
		$buildSchema = ltrim($buildSchema, '/');
		$components = preg_split('~({(?:[a-z]+)})~', $buildSchema, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$delimiters = array('/', '-', '.', '_');
		
		foreach ($components as $component) {
			$type = 'component';
			if (preg_match('~{([a-z]+)}~', $component, $matches)) {
				$component = $matches[1];
			}
			else {
				$type = 'separator';
			}
			
			$this->buildSchema[] = array(
				'type' => $type,
				'value' => $component
			);
		}
	}
	
	/**
	 * Sets the route pattern used to evaluate an incoming request.
	 * 
	 * @param	string		$pattern
	 */
	public function setPattern($pattern) {
		$this->pattern = $pattern;
	}
	
	/**
	 * Sets the list of required components.
	 * 
	 * @param	array<string>	$requiredComponents
	 */
	public function setRequiredComponents(array $requiredComponents) {
		$this->requireComponents = $requiredComponents;
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::buildLink()
	 */
	public function buildLink(array $components) {
		$application = (isset($components['application'])) ? $components['application'] : null;
		
		// drop application component to avoid being appended as query string
		unset($components['application']);
		
		// handle default values for controller
		$useBuildSchema = true;
		if (count($components) == 1 && isset($components['controller'])) {
			$ignoreController = false;
			
			if (!RequestHandler::getInstance()->isACPRequest()) {
				$landingPage = PageMenu::getInstance()->getLandingPage();
				if ($landingPage !== null && strcasecmp($landingPage->getController(), $components['controller']) == 0) {
					$ignoreController = true;
				}
				
				// check if this is the default controller of the requested application
				if (!$ignoreController && $application !== null) {
					if (RouteHandler::getInstance()->getDefaultController($application) == $components['controller']) {
						// check if this is the primary application and the landing page originates to the same application
						$primaryApplication = ApplicationHandler::getInstance()->getPrimaryApplication();
						$abbreviation = ApplicationHandler::getInstance()->getAbbreviation($primaryApplication->packageID);
						if ($abbreviation != $application || $landingPage === null || $landingPage->getApplication() != 'wcf') {
							$ignoreController = true;
						}
					}
				}
			}
			
			// drops controller from route
			if ($ignoreController) {
				$useBuildSchema = false;
				
				// unset the controller, since it would otherwise be added with http_build_query()
				unset($components['controller']);
			}
		}
		
		return $this->buildRoute($components, $application, $useBuildSchema);
	}
	
	/**
	 * Builds the actual link, the parameter $useBuildSchema can be set to false for
	 * empty routes, e.g. for the default page.
	 * 
	 * @param	array		$components
	 * @param	string		$application
	 * @param	boolean		$useBuildSchema
	 * @return	string
	 */
	protected function buildRoute(array $components, $application, $useBuildSchema) {
		$link = '';
		
		if ($useBuildSchema) {
			$lastSeparator = null;
			$skipToLastSeparator = false;
			foreach ($this->buildSchema as $component) {
				$value = $component['value'];
				
				if ($component['type'] === 'separator') {
					$lastSeparator = $value;
				}
				else if ($skipToLastSeparator === false) {
					// routes are build from left-to-right
					if (empty($components[$value])) {
						$skipToLastSeparator = true;
						
						// drop empty components to avoid them being appended as query string argument
						unset($components[$value]);
						
						continue;
					}
					
					if ($lastSeparator !== null) {
						$link .= $lastSeparator;
						$lastSeparator = null;
					}
					
					// handle controller names
					if ($value === 'controller') {
						$components[$value] = $this->getControllerName($application, $components[$value]);
					}
					
					$link .= $components[$value];
					unset($components[$value]);
				}
			}
			
			if (!empty($link) && $lastSeparator !== null) {
				$link .= $lastSeparator;
			}
		}
		
		if ($this->isACP || !URL_OMIT_INDEX_PHP) {
			if (!empty($link)) {
				$link = 'index.php?' . $link;
			}
		}
		
		if (!empty($components)) {
			if (strpos($link, '?') === false) $link .= '?';
			else $link .= '&';
			
			$link .= http_build_query($components, '', '&');
		}
		
		return $link;
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::canHandle()
	 */
	public function canHandle(array $components) {
		if (!empty($this->requireComponents)) {
			foreach ($this->requireComponents as $component => $pattern) {
				if (empty($components[$component])) {
					return false;
				}
				
				if ($pattern && !preg_match($pattern, $components[$component])) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::getRouteData()
	 */
	public function getRouteData() {
		return $this->routeData;
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::isACP()
	 */
	public function isACP() {
		return $this->isACP;
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::matches()
	 */
	public function matches($requestURL) {
		if (preg_match($this->pattern, $requestURL, $matches)) {
			foreach ($matches as $key => $value) {
				if (!is_numeric($key)) {
					$this->routeData[$key] = $value;
				}
			}
			
			$this->routeData['isDefaultController'] = (!isset($this->routeData['controller']));
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the transformed controller name.
	 * 
	 * @param	string		$application
	 * @param	string		$controller
	 * @return	string
	 */
	protected function getControllerName($application, $controller) {
		if (!isset($this->controllerNames[$controller])) {
			$controllerName = RequestHandler::getTokenizedController($controller);
			$alias = (!$this->isACP) ? RequestHandler::getInstance()->getAliasByController($controllerName) : null;
			
			$this->controllerNames[$controller] = ($alias) ?: $controllerName;
		}
		
		return $this->controllerNames[$controller];
	}
}
