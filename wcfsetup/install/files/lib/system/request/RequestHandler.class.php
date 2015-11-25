<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\menu\page\PageMenu;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Handles http requests.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class RequestHandler extends SingletonFactory {
	/**
	 * active request object
	 * @var	Request
	 */
	protected $activeRequest = null;
	
	/**
	 * @var ApplicationHandler
	 */
	protected $applicationHandler;
	
	/**
	 * @var ControllerMap
	 */
	protected $controllerMap;
	
	/**
	 * true, if current domain mismatch any known domain
	 * @var	boolean
	 */
	protected $inRescueMode = true;
	
	/**
	 * indicates if the request is an acp request
	 * @var	boolean
	 */
	protected $isACPRequest = false;
	
	/**
	 * @var RouteHandler
	 */
	protected $routeHandler;
	
	/**
	 * RequestHandler constructor.
	 * 
	 * @param       ApplicationHandler      $applicationHandler
	 * @param       ControllerMap           $controllerMap
	 * @param       RouteHandler            $routeHandler
	 */
	public function __construct(ApplicationHandler $applicationHandler, ControllerMap $controllerMap, RouteHandler $routeHandler) {
		$this->applicationHandler = $applicationHandler;
		$this->controllerMap = $controllerMap;
		$this->routeHandler = $routeHandler;
		
		parent::__construct();
	}
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		if (isset($_SERVER['HTTP_HOST'])) {
			foreach ($this->applicationHandler->getApplications() as $application) {
				if ($application->domainName == $_SERVER['HTTP_HOST']) {
					$this->inRescueMode = false;
					break;
				}
			}
			
			// check if WCF is running as standalone
			if ($this->inRescueMode() && PACKAGE_ID == 1) {
				if ($this->applicationHandler->getWCF()->domainName == $_SERVER['HTTP_HOST']) {
					$this->inRescueMode = false;
				}
			}
		}
		else {
			// when using cli, no rescue mode is provided
			$this->inRescueMode = false;
		}
		
		if (class_exists('wcf\system\WCFACP', false)) {
			$this->isACPRequest = true;
		}
	}
	
	/**
	 * Handles a http request.
	 *
	 * @param	string		$application
	 * @param	boolean		$isACPRequest
	 * @throws      AJAXException
	 * @throws      IllegalLinkException
	 * @throws      SystemException
	 */
	public function handle($application = 'wcf', $isACPRequest = false) {
		$this->isACPRequest = $isACPRequest;
		
		// initialize route handler
		$this->routeHandler->setRequestHandler($this);
		$this->routeHandler->setDefaultRoutes();
		
		if (!$this->routeHandler->matches($application)) {
			if (ENABLE_DEBUG_MODE) {
				throw new SystemException("Cannot handle request, no valid route provided.");
			}
			else {
				throw new IllegalLinkException();
			}
		}
		
		// build request
		$this->buildRequest($application);
		
		// handle offline mode
		if (!$isACPRequest && defined('OFFLINE') && OFFLINE) {
			if (!WCF::getSession()->getPermission('admin.general.canViewPageDuringOfflineMode') && !$this->activeRequest->isAvailableDuringOfflineMode()) {
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
					throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					@header('HTTP/1.1 503 Service Unavailable');
					WCF::getTPL()->assign(array(
						'templateName' => 'offline',
						'templateNameApplication' => 'wcf'
					));
					WCF::getTPL()->display('offline');
				}
				
				exit;
			}
		}
		
		// start request
		$this->activeRequest->execute();
	}
	
	/**
	 * Builds a new request.
	 * 
	 * @param	string		$application
	 * @throws      IllegalLinkException
	 */
	protected function buildRequest($application) {
		try {
			$routeData = $this->routeHandler->getRouteData();
			
			// handle landing page for frontend requests
			if (!$this->isACPRequest()) {
				$this->handleDefaultController($application, $routeData);
				
				// check if accessing from the wrong domain (e.g. "www." omitted but domain was configured with)
				if (!defined('WCF_RUN_MODE') || WCF_RUN_MODE != 'embedded') {
					$applicationObject = $this->applicationHandler->getApplication($application);
					if ($applicationObject->domainName != $_SERVER['HTTP_HOST']) {
						// build URL, e.g. http://example.net/forum/
						$url = FileUtil::addTrailingSlash(RouteHandler::getProtocol() . $applicationObject->domainName . RouteHandler::getPath());
						
						// query string, e.g. ?foo=bar
						if (!empty($_SERVER['QUERY_STRING'])) {
							$url .= '?' . $_SERVER['QUERY_STRING'];
						}
						
						HeaderUtil::redirect($url, true);
						exit;
					}
				}
				
				// handle controller aliasing
				/*
				if (empty($routeData['isImplicitController']) && isset($routeData['controller'])) {
					$ciController = mb_strtolower($routeData['controller']);
					
					// aliased controller, redirect to new URL
					$alias = $this->getAliasByController($ciController);
					if ($alias !== null) {
						$this->redirect($routeData, $application);
					}
					
					$controller = $this->getControllerByAlias($ciController);
					if ($controller !== null) {
						// check if controller was provided explicitly as it should
						$alias = $this->getAliasByController($controller);
						if ($alias != $routeData['controller']) {
							$routeData['controller'] = $controller;
							$this->redirect($routeData, $application);
						}
						
						$routeData['controller'] = $controller;
					}
				}
				*/
			}
			else if (empty($routeData['controller'])) {
				$routeData['controller'] = 'index';
			}
			
			$controller = $routeData['controller'];
			
			$classData = $this->controllerMap->resolve($application, $controller, $this->isACPRequest());
			
			// check if controller was provided exactly as it should
			/*
			if (!URL_LEGACY_MODE && !$this->isACPRequest()) {
				if (preg_match('~([A-Za-z0-9]+)(?:Action|Form|Page)$~', $classData['className'], $matches)) {
					$realController = self::getTokenizedController($matches[1]);
					
					if ($controller != $realController) {
						$this->redirect($routeData, $application, $matches[1]);
					}
				}
			}
			*/
			
			$this->activeRequest = new Request($classData['className'], $classData['controller'], $classData['pageType']);
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Redirects to the actual URL, e.g. controller has been aliased or mistyped (boardlist instead of board-list).
	 * 
	 * @param	array<string>		$routeData
	 * @param	string			$application
	 * @param	string			$controller
	 */
	protected function redirect(array $routeData, $application, $controller = null) {
		$routeData['application'] = $application;
		if ($controller !== null) $routeData['controller'] = $controller;
		
		// append the remaining query parameters
		foreach ($_GET as $key => $value) {
			if (!empty($value) && $key != 'controller') {
				$routeData[$key] = $value;
			}
		}
		
		$redirectURL = LinkHandler::getInstance()->getLink($routeData['controller'], $routeData);
		HeaderUtil::redirect($redirectURL, true);
		exit;
	}
	
	/**
	 * Checks page access for possible mandatory redirects.
	 * 
	 * @param	string		$application
	 * @param	array		$routeData
	 */
	protected function handleDefaultController($application, array &$routeData) {
		if (!$this->routeHandler->isDefaultController()) {
			return;
		}
		
		// loading the PageMenu object as a dependency in ACP requests breaks everything
		$landingPage = WCF::getDIContainer()->get(PageMenu::class)->getLandingPage();
		if ($landingPage === null) {
			return;
		}
		
		if (empty($routeData['controller'])) $routeData['isImplicitController'] = true;
		
		// resolve implicit application abbreviation for landing page controller
		$landingPageApplication = $landingPage->getApplication();
		$primaryApplication = $this->applicationHandler->getPrimaryApplication();
		$primaryApplicationAbbr = $this->applicationHandler->getAbbreviation($primaryApplication->packageID);
		if ($landingPageApplication == 'wcf') {
			$landingPageApplication = $primaryApplicationAbbr;
		}
		
		// check if currently invoked application matches the landing page
		if ($landingPageApplication == $application) {
			$routeData['controller'] = $landingPage->getController();
			$routeData['controller'] = $this->controllerMap->lookup($routeData['controller']);
			
			return;
		}
		
		// redirect if this is the primary application
		if ($application === $primaryApplicationAbbr) {
			HeaderUtil::redirect($landingPage->getLink());
			exit;
		}
		
		// set default controller
		$applicationObj = WCF::getApplicationObject($this->applicationHandler->getApplication($application));
		$routeData['controller'] = preg_replace('~^.*?\\\([^\\\]+)(?:Action|Form|Page)$~', '\\1', $applicationObj->getPrimaryController());
		$routeData['controller'] = $this->controllerMap->lookup($routeData['controller']);
	}
	
	/**
	 * Returns the active request object.
	 * 
	 * @return	\wcf\system\request\Request
	 */
	public function getActiveRequest() {
		return $this->activeRequest;
	}
	
	/**
	 * Returns true if the request is an acp request.
	 * 
	 * @return	boolean
	 */
	public function isACPRequest() {
		return $this->isACPRequest;
	}
	
	/**
	 * Returns true, if current host mismatches any known domain.
	 * 
	 * @return	boolean
	 */
	public function inRescueMode() {
		return $this->inRescueMode;
	}
}
