<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ControllerCacheBuilder;
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
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class RequestHandler extends SingletonFactory {
	/**
	 * active request object
	 * @var	\wcf\system\request\Request
	 */
	protected $activeRequest = null;
	
	/**
	 * list of known controllers grouped by application and type
	 * @var	array<array>
	 */
	protected $controllers = null;
	
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
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		if (isset($_SERVER['HTTP_HOST'])) {
			foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
				if ($application->domainName == $_SERVER['HTTP_HOST']) {
					$this->inRescueMode = false;
					break;
				}
			}
			
			// check if WCF is running as standalone
			if ($this->inRescueMode() && PACKAGE_ID == 1) {
				if (ApplicationHandler::getInstance()->getWCF()->domainName == $_SERVER['HTTP_HOST']) {
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
		
		if (PACKAGE_ID) {
			$this->controllers = ControllerCacheBuilder::getInstance()->getData(array(
				'environment' => ($this->isACPRequest ? 'admin' : 'user')
			));
		}
	}
	
	/**
	 * Handles a http request.
	 * 
	 * @param	string		$application
	 * @param	boolean		$isACPRequest
	 */
	public function handle($application = 'wcf', $isACPRequest = false) {
		$this->isACPRequest = $isACPRequest;
		
		if (!RouteHandler::getInstance()->matches()) {
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
						'templateName' => 'offline'
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
	 */
	protected function buildRequest($application) {
		try {
			$routeData = RouteHandler::getInstance()->getRouteData();
			
			// handle landing page for frontend requests
			if (!$this->isACPRequest()) {
				$this->handleDefaultController($application, $routeData);
				
				// check if accessing from the wrong domain (e.g. "www." omitted but domain was configured with)
				if (!defined('WCF_RUN_MODE') || WCF_RUN_MODE != 'embedded') {
					$applicationObject = ApplicationHandler::getInstance()->getApplication($application);
					if ($applicationObject->domainName != $_SERVER['HTTP_HOST']) {
						// build URL, e.g. http://example.net/forum/
						$url = FileUtil::addTrailingSlash(RouteHandler::getProtocol() . $applicationObject->domainName . RouteHandler::getPath());
						
						if (URL_LEGACY_MODE) {
							// add path info, e.g. index.php/Board/2/
							$pathInfo = RouteHandler::getPathInfo();
							if (!empty($pathInfo)) {
								$url .= 'index.php' . $pathInfo;
							}
						}
						
						// query string, e.g. ?foo=bar
						if (!empty($_SERVER['QUERY_STRING'])) {
							$url .= '?' . $_SERVER['QUERY_STRING'];
						}
						
						HeaderUtil::redirect($url, true);
						exit;
					}
				}
			}
			
			$controller = $routeData['controller'];
			
			// validate class name
			if (!preg_match('~^[a-z0-9_]+$~i', $controller)) {
				throw new SystemException("Illegal class name '".$controller."'");
			}
			
			// find class
			$classData = $this->getClassData($controller, 'page', $application);
			if ($classData === null) $classData = $this->getClassData($controller, 'form', $application);
			if ($classData === null) $classData = $this->getClassData($controller, 'action', $application);
			
			if ($classData === null) {
				throw new SystemException("unable to find class for controller '".$controller."'");
			}
			else if (!class_exists($classData['className'])) {
				throw new SystemException("unable to find class '".$classData['className']."'");
			}
			
			$this->activeRequest = new Request($classData['className'], $classData['controller'], $classData['pageType']);
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Checks page access for possible mandatory redirects.
	 * 
	 * @param	string		$application
	 * @param	array		$routeData
	 */
	protected function handleDefaultController($application, array &$routeData) {
		if (!RouteHandler::getInstance()->isDefaultController()) {
			return;
		}
		
		$landingPage = PageMenu::getInstance()->getLandingPage();
		if ($landingPage === null) {
			return;
		}
		
		// resolve implicit application abbreviation for landing page controller
		$landingPageApplication = $landingPage->getApplication();
		if ($landingPageApplication == 'wcf') {
			$primaryApplication = ApplicationHandler::getInstance()->getPrimaryApplication();
			$landingPageApplication = ApplicationHandler::getInstance()->getAbbreviation($primaryApplication->packageID);
		}
		
		// check if currently invoked application matches the landing page
		if ($landingPageApplication == $application) {
			$routeData['controller'] = $landingPage->getController();
			return;
		}
		
		// assign the default controller
		$currentApplication = ApplicationHandler::getInstance()->getApplication($application);
		if ($controller = WCF::getApplicationObject($currentApplication)->getPrimaryController()) {
			$controller = explode('\\', $controller);
			
			if (URL_LEGACY_MODE) {
				HeaderUtil::redirect(LinkHandler::getInstance()->getLink(preg_replace('~(Action|Form|Page)$~', '', array_pop($controller)), array('application' => $controller[0])));
				exit;
			}
			else {
				$routeData['controller'] = preg_replace('~(Action|Form|Page)$~', '', array_pop($controller));
				return;
			}
		}
		
		HeaderUtil::redirect($landingPage->getLink());
		exit;
	}
	
	/**
	 * Returns the class data for the active request or null if for the given
	 * configuration no proper class exist.
	 * 
	 * @param	string		$controller
	 * @param	string		$pageType
	 * @param	string		$application
	 * @return	array
	 */
	protected function getClassData($controller, $pageType, $application) {
		$className = false;
		
		if ($this->controllers !== null) {
			$className = $this->lookupController($controller, $pageType, $application);
			if ($className === false && $application != 'wcf') {
				$className = $this->lookupController($controller, $pageType, 'wcf');
			}
		}
		
		// controller is either unknown or within WCFSetup
		if ($className === false) {
			$className = $application.'\\'.($this->isACPRequest() ? 'acp\\' : '').$pageType.'\\'.ucfirst($controller).ucfirst($pageType);
			if ($application != 'wcf' && !class_exists($className)) {
				$className = 'wcf\\'.($this->isACPRequest() ? 'acp\\' : '').$pageType.'\\'.ucfirst($controller).ucfirst($pageType);
			}
		}
		
		if (!class_exists($className)) {
			return null;
		}
		
		// check whether the class is abstract
		$reflectionClass = new \ReflectionClass($className);
		if ($reflectionClass->isAbstract()) {
			return null;
		}
		
		return array(
			'className' => $className,
			'controller' => $controller,
			'pageType' => $pageType
		);
	}
	
	/**
	 * Lookups a controller from the list of known controllers using a case-insensitive search.
	 * 
	 * @param	string		$controller
	 * @param	string		$pageType
	 * @param	string		$application
	 * @return	boolean
	 */
	protected function lookupController($controller, $pageType, $application) {
		if (isset($this->controllers[$application]) && isset($this->controllers[$application][$pageType])) {
			$ciController = mb_strtolower($controller);
			if (isset($this->controllers[$application][$pageType][$ciController])) {
				return $this->controllers[$application][$pageType][$ciController];
			}
		}
		
		return false;
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
