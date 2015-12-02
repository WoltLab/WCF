<?php
namespace wcf\system\request;
use wcf\page\CmsPage;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

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
class ControllerMap extends SingletonFactory {
	/**
	 * @var string[][]
	 */
	protected $ciControllers;
	
	/**
	 * @var string[][]
	 */
	protected $customUrls;
	
	/**
	 * list of <ControllerName> to <controller-name> mappings
	 * @var array<string>
	 */
	protected $lookupCache = [];
	
	/**
	 * @inheritDoc
	 * @throws      SystemException
	 */
	protected function init() {
		$this->ciControllers = RoutingCacheBuilder::getInstance()->getData([], 'ciControllers');
		$this->customUrls = RoutingCacheBuilder::getInstance()->getData([], 'customUrls');
	}
	
	/**
	 * Resolves class data for given controller.
	 * 
	 * URL -> Controller
	 * 
	 * @param       string          $application    application identifier
	 * @param       string          $controller     url controller
	 * @param       boolean         $isAcpRequest   true if this is an ACP request
	 * @return      mixed           array containing className, controller and pageType or a string containing the controller name for aliased controllers
	 * @throws      SystemException
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
			throw new SystemException("Unknown controller '" . $controller . "'");
		}
		else {
			// handle controllers with a custom url
			$controller = $classData['controller'];
			
			if (isset($this->customUrls['reverse'][$application]) && isset($this->customUrls['reverse'][$application][$controller])) {
				return $this->customUrls['reverse'][$application][$controller];
			}
			else if ($application !== 'wcf') {
				if (isset($this->customUrls['reverse']['wcf']) && isset($this->customUrls['reverse']['wcf'][$controller])) {
					return $this->customUrls['reverse']['wcf'][$controller];
				}
			}
		}
		
		return $classData;
	}
	
	/**
	 * Attempts to resolve a custom controller, will return an empty array
	 * regardless if given controller would match an actual controller class.
	 * 
	 * URL -> Controller
	 * 
	 * @param       string  $application    application identifier
	 * @param       string  $controller     url controller
	 * @return      array   empty array if there is no exact match
	 */
	public function resolveCustomController($application, $controller) {
		if (isset($this->customUrls['lookup'][$application]) && isset($this->customUrls['lookup'][$application][$controller])) {
			$data = $this->customUrls['lookup'][$application][$controller];
			if (preg_match('~^__WCF_CMS__(?P<pageID>\d+)-(?P<languageID>\d+)$~', $data, $matches)) {
				return [
					'className' => CmsPage::class,
					'controller' => 'cms',
					'pageType' => 'page',
					'languageID' => $matches['languageID'],
					'pageID' => $matches['pageID']
				];
			}
			else {
				preg_match('~([^\\\]+)(Action|Form|Page)$~', $data, $matches);
				
				return [
					'className' => $data,
					'controller' => $matches[1],
					'pageType' => strtolower($matches[2])
				];
			}
		}
		
		return [];
	}
	
	/**
	 * Transforms given controller into its url representation.
	 * 
	 * Controller -> URL
	 * 
	 * @param       string          $application    application identifier
	 * @param       string          $controller     controller class, e.g. 'MembersList'
	 * @return      string          url representation of controller, e.g. 'members-list'
	 */
	public function lookup($application, $controller) {
		$lookupKey = $application . '-' . $controller;
		
		if (isset($this->lookupCache[$lookupKey])) {
			return $this->lookupCache[$lookupKey];
		}
		
		if (isset($this->customUrls['reverse'][$application]) && isset($this->customUrls['reverse'][$application][$controller])) {
			$urlController = $this->customUrls['reverse'][$application][$controller];
		}
		else {
			$parts = preg_split('~([A-Z][a-z0-9]+)~', $controller, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$parts = array_map('strtolower', $parts);
			
			$urlController = implode('-', $parts);
		}
		
		$this->lookupCache[$lookupKey] = $urlController;
		
		return $urlController;
	}
	
	/**
	 * Looks up a cms page URL, returns an array containing the application identifier
	 * and url controller name or null if there was no match.
	 * 
	 * @param       integer         $pageID         page id
	 * @param       integer         $languageID     content language id
	 * @return      string[]|null
	 */
	public function lookupCmsPage($pageID, $languageID) {
		$key = '__WCF_CMS__' . $pageID . '-' . ($languageID ?: 0);
		foreach ($this->customUrls['reverse'] as $application => $reverseURLs) {
			if (isset($reverseURLs[$key])) {
				return [
					'application' => $application,
					'controller' => $reverseURLs[$key]
				];
			}
		}
		
		return null;
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
