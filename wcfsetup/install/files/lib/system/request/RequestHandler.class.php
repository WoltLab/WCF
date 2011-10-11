<?php
namespace wcf\system\request;
use wcf\system\exception\SystemException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\SingletonFactory;

/**
 * Handles http requests.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
class RequestHandler extends SingletonFactory {
	/**
	 * active request object
	 * @var wcf\system\request\Request
	 */
	protected $activeRequest = null;
	
	/**
	 * Handles a http request
	 *
	 * @param	string		$application
	 * @param	boolean		$isACP
	 */
	public function handle($application = 'wcf', $isACP = false) {
		if (!RouteHandler::getInstance()->matches($isACP)) {
			throw new SystemException("Cannot handle request, no valid route provided.");
		}
		
		// build request
		$this->buildRequest($application, $isACP);
		// start request
		$this->activeRequest->execute();
	}
	
	/**
	 * Builds a new request.
	 *
	 * @param 	string 		$application
	 * @param	boolean		$isACP
	 */
	protected function buildRequest($application, $isACP) {
		try {
			$routeData = RouteHandler::getInstance()->getRouteData();
			$controller = $routeData['controller'];
			
			// validate class name
			if (!preg_match('~^[a-z0-9_]+$~i', $controller)) {
				throw new SystemException("Illegal class name '".$controller."'");
			}
			
			// find class
			$classData = $this->getClassData($controller, 'page', $application, $isACP);
			if ($classData === null) $classData = $this->getClassData($controller, 'form', $application, $isACP);
			if ($classData === null) $classData = $this->getClassData($controller, 'action', $application, $isACP);
			
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
	
	protected function getClassData($controller, $pageType, $application, $isACP) {
		$className = $application.'\\'.($isACP ? 'acp\\' : '').$pageType.'\\'.ucfirst($controller).ucfirst($pageType);
		if ($application != 'wcf' && !class_exists($className)) {
			$className = 'wcf\\'.($isACP ? 'acp\\' : '').$pageType.'\\'.ucfirst($controller).ucfirst($pageType);
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
}
