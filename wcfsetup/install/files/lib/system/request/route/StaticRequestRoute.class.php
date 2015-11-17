<?php
namespace wcf\system\request\route;
use wcf\system\application\ApplicationHandler;
use wcf\system\menu\page\PageMenu;
use wcf\system\request\ControllerMap;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;

/**
 * Static route implementation to resolve HTTP requests, handling a single controller.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class StaticRequestRoute extends DynamicRequestRoute {
	/**
	 * @var \wcf\system\request\ControllerMap
	 */
	protected $controllerMap;
	
	/**
	 * static application identifier
	 * @var	string
	 */
	protected $staticApplication = '';
	
	/**
	 * static controller name, not the FQN
	 * @var	string
	 */
	protected $staticController = '';
	
	/**
	 * StaticRequestRoute constructor.
	 * 
	 * @param       \wcf\system\application\ApplicationHandler      $applicationHandler
	 * @param       \wcf\system\request\ControllerMap               $controllerMap
	 * @param       \wcf\system\menu\page\PageMenu                  $pageMenu
	 * @param       \wcf\system\request\RequestHandler              $requestHandler
	 * @param       \wcf\system\request\RouteHandler                $routeHandler
	 */
	public function __construct(ApplicationHandler $applicationHandler, ControllerMap $controllerMap, PageMenu $pageMenu, RequestHandler $requestHandler, RouteHandler $routeHandler) {
		parent::__construct($applicationHandler, $pageMenu, $requestHandler, $routeHandler);
		
		$this->controllerMap = $controllerMap;
	}
	
	/**
	 * @see \wcf\system\request\route\IRequestRoute::setIsACP()
	 */
	public function setIsACP($isACP) {
		// static routes are disallowed for ACP
		parent::setIsACP(false);
	}
	
	/**
	 * Sets the static controller for this route.
	 *
	 * @param	string		$application
	 * @param	string		$controller
	 */
	public function setStaticController($application, $controller) {
		$this->staticApplication = $application;
		$this->staticController = $controller;
		
		$this->requireComponents['controller'] = '~^' . $this->staticController . '$~';
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::buildLink()
	 */
	public function buildLink(array $components) {
		// static routes don't have these components
		unset($components['application']);
		unset($components['controller']);
		
		return $this->buildRoute($components, '', true);
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::canHandle()
	 */
	public function canHandle(array $components) {
		if (isset($components['application']) && $components['application'] == $this->staticApplication) {
			if (isset($components['controller']) && $components['controller'] == $this->staticController) {
				return parent::canHandle($components);
			}
		}
		
		return false;
	}
	
	/**
	 * @see	\wcf\system\request\IRoute::matches()
	 */
	public function matches($requestURL) {
		if (parent::matches($requestURL)) {
			$this->routeData['application'] = $this->staticApplication;
			$this->routeData['controller'] = $this->controllerMap->lookup($this->staticController);
			$this->routeData['isDefaultController'] = false;
			
			return true;
		}
		
		return false;
	}
}
