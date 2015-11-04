<?php
namespace wcf\system\request;
use wcf\system\exception\SystemException;

/**
 * Resolves incoming requests and performs lookups for controller to url mappings.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class ControllerMap {
	/**
	 * list of <ControllerName> to <controller-name> mappings
	 * @var array<string>
	 */
	protected $lookupCache = [];
	
	public function __construct() {
		// TODO: initialize custom controller mappings
	}
	
	/**
	 * Resolves class data for given controller.
	 * 
	 * @param       string          $application    application identifier
	 * @param       string          $controller     url controller
	 * @param       boolean         $isAcpRequest   true if this is an ACP request
	 * @return      array<string>   className, controller and pageType
	 */
	public function resolve($application, $controller, $isAcpRequest) {
		// validate controller
		if (!preg_match('~^[a-z][a-z0-9]+(?:\-[a-z][a-z0-9]+)*$~', $controller)) {
			throw new SystemException("Malformed controller name '" . $controller . "'");
		}
		
		$parts = explode('-', $controller);
		$parts = array_map('ucfirst', $parts);
		$controller = implode('', $parts);
		
		if ($controller === 'AjaxProxy') $controller = 'AJAXProxy';
		
		$classData = $this->getClassData($application, $controller, $isAcpRequest, 'page');
		if ($classData === null) $classData = $this->getClassData($application, $controller, $isAcpRequest, 'form');
		if ($classData === null) $classData = $this->getClassData($application, $controller, $isAcpRequest, 'action');
		
		if ($classData === null) {
			// TODO: check custom controller mappings
			
			throw new SystemException("Unknown controller '" . $controller . "'");
		}
		else {
			// TODO: check if controller was aliased and force a redirect
		}
		
		return $classData;
	}
	
	/**
	 * Transforms given controller into its url representation.
	 * 
	 * @param       string          $controller     controller class, e.g. 'MembersList'
	 * @return      string          url representation of controller, e.g. 'members-list'
	 */
	public function lookup($controller) {
		if (isset($this->lookupCache[$controller])) {
			return $this->lookupCache[$controller];
		}
		
		$parts = preg_split('~([A-Z][a-z0-9]+)~', $controller, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$parts = array_map('strtolower', $parts);
		
		$urlController = implode('-', $parts);
		
		// TODO: lookup custom controller mappings
		
		$this->lookupCache[$controller] = $urlController;
		
		return $urlController;
	}
	
	/**
	 * Returns the class data for the active request or null if for the given
	 * configuration no proper class exist.
	 *
	 * @param	string		$application    application identifier
	 * @param	string		$controller     controller name
	 * @param       boolean         $isAcpRequest   true if this is an ACP request
	 * @param	string		$pageType       page type, e.g. 'form' or 'action'
	 * @return	array<string>   className, controller and pageType
	 */
	protected function getClassData($application, $controller, $isAcpRequest, $pageType) {
		$className = $application . '\\' . ($isAcpRequest ? 'acp\\' : '') . $pageType . '\\' . $controller . ucfirst($pageType);
		if (!class_exists($className)) {
			if ($application === 'wcf') {
				return null;
			}
			
			$className = 'wcf\\' . ($isAcpRequest ? 'acp\\' : '') . $pageType . '\\' . $controller . ucfirst($pageType);
			if (!class_exists($className)) {
				return null;
			}
		}
		
		// check for abstract classes
		$reflectionClass = new \ReflectionClass($className);
		if ($reflectionClass->isAbstract()) {
			return null;
		}
		
		return [
			'className' => $className,
			'controller' => $controller,
			'pageType' => $pageType
		];
	}
}
