<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\box\BoxHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\notice\NoticeHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Handles http requests.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
 */
class RequestHandler extends SingletonFactory {
	/**
	 * active request object
	 * @var	Request
	 */
	protected $activeRequest;
	
	/**
	 * true, if current domain mismatch any known domain
	 * @var	boolean
	 */
	protected $inRescueMode = false;
	
	/**
	 * indicates if the request is an acp request
	 * @var	boolean
	 */
	protected $isACPRequest = false;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->isACPRequest = class_exists('wcf\system\WCFACP', false);
	}
	
	/**
	 * Handles a http request.
	 *
	 * @param	string		$application
	 * @param	boolean		$isACPRequest
	 * @throws	AJAXException
	 * @throws	IllegalLinkException
	 * @throws	SystemException
	 */
	public function handle($application = 'wcf', $isACPRequest = false) {
		try {
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
						throw new AJAXException(WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
					}
					else {
						@header('HTTP/1.1 503 Service Unavailable');
						BoxHandler::disablePageLayout();
						NoticeHandler::disableNotices();
						WCF::getTPL()->assign([
							'templateName' => 'offline',
							'templateNameApplication' => 'wcf'
						]);
						WCF::getTPL()->display('offline');
					}
					
					exit;
				}
			}
			
			// start request
			$this->activeRequest->execute();
		}
		catch (NamedUserException $e) {
			$e->show();
			exit;
		}
	}
	
	/**
	 * Builds a new request.
	 * 
	 * @param	string		$application
	 * @throws	IllegalLinkException
	 * @throws	SystemException
	 */
	protected function buildRequest($application) {
		try {
			$routeData = RouteHandler::getInstance()->getRouteData();
			
			// handle landing page for frontend requests
			if (!$this->isACPRequest()) {
				$this->handleDefaultController($application, $routeData);
				
				// check if accessing from the wrong domain (e.g. "www." omitted but domain was configured with)
				if (!defined('WCF_RUN_MODE') || WCF_RUN_MODE !== 'embedded') {
					$applicationObject = ApplicationHandler::getInstance()->getApplication($application);
					if ($applicationObject->domainName != $_SERVER['HTTP_HOST']) {
						// build URL, e.g. http://example.net/forum/
						$url = FileUtil::addTrailingSlash(RouteHandler::getProtocol() . $applicationObject->domainName . RouteHandler::getPath());
						
						// query string, e.g. ?foo=bar
						if (!empty($_SERVER['QUERY_STRING'])) {
							$url .= '?' . $_SERVER['QUERY_STRING'];
						}
						
						HeaderUtil::redirect($url, true, false);
						exit;
					}
				}
			}
			else if (empty($routeData['controller'])) {
				$routeData['controller'] = 'index';
			}
			
			$controller = $routeData['controller'];
			
			if (isset($routeData['className'])) {
				$classData = [
					'className' => $routeData['className'],
					'controller' => $routeData['controller'],
					'pageType' => $routeData['pageType']
				];
				
				unset($routeData['className']);
				unset($routeData['controller']);
				unset($routeData['pageType']);
			}
			else {
				if ($this->isACPRequest() && ($controller === 'login' || $controller === 'index') && $application !== 'wcf') {
					HeaderUtil::redirect(
						LinkHandler::getInstance()->getLink(ucfirst($controller)),
						true,
						false
					);
					exit;
				}
				
				$classData = ControllerMap::getInstance()->resolve($application, $controller, $this->isACPRequest(), RouteHandler::getInstance()->isRenamedController());
				if (is_string($classData)) {
					$this->redirect($routeData, $application, $classData);
				}
			}
			
			// handle CMS page meta data
			$metaData = ['isDefaultController' => (!empty($routeData['isDefaultController']))];
			if (isset($routeData['cmsPageID'])) {
				$metaData['cms'] = [
					'pageID' => $routeData['cmsPageID'],
					'languageID' => $routeData['cmsPageLanguageID']
				];
				
				if ($routeData['cmsPageLanguageID'] && $routeData['cmsPageLanguageID'] != WCF::getLanguage()->languageID) {
					WCF::setLanguage($routeData['cmsPageLanguageID']);
				}
				
				unset($routeData['cmsPageID']);
				unset($routeData['cmsPageLanguageID']);
			}
			
			$this->activeRequest = new Request($classData['className'], $classData['controller'], $classData['pageType'], $metaData);
			
			if (!$this->isACPRequest()) {
				// determine if current request matches the landing page
				if (ControllerMap::getInstance()->isLandingPage($classData, $metaData)) {
					$this->activeRequest->setIsLandingPage();
				}
			}
		}
		catch (SystemException $e) {
			if (defined('ENABLE_DEBUG_MODE') && ENABLE_DEBUG_MODE && defined('ENABLE_DEVELOPER_TOOLS') && ENABLE_DEVELOPER_TOOLS) {
				throw $e;
			}
			
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Redirects to the actual URL, e.g. controller has been aliased or mistyped (boardlist instead of board-list).
	 * 
	 * @param	string[]	$routeData
	 * @param	string		$application
	 * @param	string		$controller
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
		HeaderUtil::redirect($redirectURL, true, false);
		exit;
	}
	
	/**
	 * Checks page access for possible mandatory redirects.
	 * 
	 * @param	string		$application
	 * @param	string[]	$routeData
	 * @throws	IllegalLinkException
	 */
	protected function handleDefaultController($application, array &$routeData) {
		if (!RouteHandler::getInstance()->isDefaultController()) {
			return;
		}
		
		$data = ControllerMap::getInstance()->lookupDefaultController($application);
		if ($data === null) {
			// handle WCF which does not have a default controller
			throw new IllegalLinkException();
		}
		else if (!empty($data['redirect'])) {
			// force a redirect
			HeaderUtil::redirect($data['redirect'], true, false);
			exit;
		}
		else if (!empty($data['application']) && $data['application'] !== $application) {
			HeaderUtil::redirect(
				LinkHandler::getInstance()->getLink(
					ControllerMap::getInstance()->resolve($data['application'], $data['controller'], false)['controller'],
					['application' => $data['application']]
				), true, true
			);
			exit;
		}
		
		// copy route data
		foreach ($data as $key => $value) {
			$routeData[$key] = $value;
		}
		
		$routeData['isDefaultController'] = true;
	}
	
	/**
	 * Returns the active request object.
	 * 
	 * @return	Request
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
