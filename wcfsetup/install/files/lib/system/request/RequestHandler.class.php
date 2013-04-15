<?php
namespace wcf\system\request;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\menu\page\PageMenu;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Handles http requests.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class RequestHandler extends SingletonFactory {
	/**
	 * active request object
	 * @var	wcf\system\request\Request
	 */
	protected $activeRequest = null;
	
	/**
	 * indicates if the request is an acp request
	 * @var	boolean
	 */
	protected $isACPRequest = false;
	
	/**
	 * Handles a http request.
	 *
	 * @param	string		$application
	 * @param	boolean		$isACPRequest
	 */
	public function handle($application = 'wcf', $isACPRequest = false) {
		$this->isACPRequest = $isACPRequest;
		
		if (!RouteHandler::getInstance()->matches()) {
			throw new SystemException("Cannot handle request, no valid route provided.");
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
				$landingPage = PageMenu::getInstance()->getLandingPage();
				if ($landingPage !== null && RouteHandler::getInstance()->isDefaultController()) {
					// check if redirect URL matches current URL
					$redirectURL = $landingPage->getLink();
					if (StringUtil::replace(RouteHandler::getHost(), '', $redirectURL) == $_SERVER['REQUEST_URI']) {
						$routeData['controller'] = $landingPage->getController();
					}
					else {
						// redirect to landing page
						HeaderUtil::redirect($landingPage->getLink(), true);
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
	 * Returns the class data for the active request or null if for the given
	 * configuration no proper class exist.
	 * 
	 * @param	string		$controller
	 * @param	string		$pageType
	 * @param	string		$application
	 * @return	array
	 */
	protected function getClassData($controller, $pageType, $application) {
		$className = $application.'\\'.($this->isACPRequest() ? 'acp\\' : '').$pageType.'\\'.ucfirst($controller).ucfirst($pageType);
		if ($application != 'wcf' && !class_exists($className)) {
			$className = 'wcf\\'.($this->isACPRequest() ? 'acp\\' : '').$pageType.'\\'.ucfirst($controller).ucfirst($pageType);
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
	 * Returns the active request object.
	 * 
	 * @return	wcf\system\request\Request
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
}
