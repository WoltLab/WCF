<?php
namespace wcf\system\request;
use wcf\page\CmsPage;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Resolves incoming requests and performs lookups for controller to url mappings.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 * @since	2.2
 */
class ControllerMap extends SingletonFactory {
	/**
	 * @var	string[][]
	 */
	protected $ciControllers;
	
	/**
	 * @var	string[][]
	 */
	protected $customUrls;
	
	/**
	 * @var	string[]
	 */
	protected $landingPages;
	
	/**
	 * list of <ControllerName> to <controller-name> mappings
	 * @var	string[]
	 */
	protected $lookupCache = [];
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function init() {
		$this->ciControllers = RoutingCacheBuilder::getInstance()->getData([], 'ciControllers');
		$this->customUrls = RoutingCacheBuilder::getInstance()->getData([], 'customUrls');
		$this->landingPages = RoutingCacheBuilder::getInstance()->getData([], 'landingPages');
	}
	
	/**
	 * Resolves class data for given controller.
	 * 
	 * URL -> Controller
	 * 
	 * @param	string		$application	application identifier
	 * @param	string		$controller	url controller
	 * @param	boolean		$isAcpRequest	true if this is an ACP request
	 * @return	mixed           array containing className, controller and pageType or a string containing the controller name for aliased controllers
	 * @throws	SystemException
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
	 * @param	string		$application	application identifier
	 * @param	string		$controller	url controller
	 * @return	array		empty array if there is no exact match
	 */
	public function resolveCustomController($application, $controller) {
		if (isset($this->customUrls['lookup'][$application]) && isset($this->customUrls['lookup'][$application][$controller])) {
			$data = $this->customUrls['lookup'][$application][$controller];
			if (preg_match('~^__WCF_CMS__(?P<pageID>\d+)-(?P<languageID>\d+)$~', $data, $matches)) {
				return [
					'className' => CmsPage::class,
					'controller' => 'cms',
					'pageType' => 'page',
					
					// CMS page meta data
					'cmsPageID' => $matches['pageID'],
					'cmsPageLanguageID' => $matches['languageID']
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
	 * @param	string		$application	application identifier
	 * @param	string		$controller	controller class, e.g. 'MembersList'
	 * @return	string		url representation of controller, e.g. 'members-list'
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
			$urlController = self::transformController($controller);
		}
		
		$this->lookupCache[$lookupKey] = $urlController;
		
		return $urlController;
	}
	
	/**
	 * Looks up a cms page URL, returns an array containing the application identifier
	 * and url controller name or null if there was no match.
	 * 
	 * @param	integer		$pageID		page id
	 * @param	integer		$languageID	content language id
	 * @return	string[]|null
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
	 * Lookups default controller for given application.
	 * 
	 * @param	string		$application	application identifier
	 * @return	null|string[]	default controller
	 * @throws	SystemException
	 */
	public function lookupDefaultController($application) {
		$controller = $this->landingPages[$application][1];
		
		if ($application === 'wcf' && empty($controller)) {
			return null;
		}
		else if (preg_match('~^__WCF_CMS__(?P<pageID>\d+)$~', $controller, $matches)) {
			$cmsPageData = $this->lookupCmsPage($matches['pageID'], 0);
			if ($cmsPageData === null) {
				// page is multilingual, use current language id to resolve request
				$cmsPageData = $this->lookupCmsPage($matches['pageID'], WCF::getLanguage()->languageID);
				
				if ($cmsPageData === null) {
					throw new SystemException("Unable to resolve CMS page");
				}
			}
			
			// different application, redirect instead
			if ($cmsPageData['application'] !== $application) {
				return ['redirect' => LinkHandler::getInstance()->getCmsLink($matches['pageID'])];
			}
			else {
				return $this->resolveCustomController($cmsPageData['application'], $cmsPageData['controller']);
			}
		}
		
		return [
			'application' => $application,
			'controller' => $controller
		];
	}
	
	/**
	 * Returns true if given controller is the application's default.
	 * 
	 * @param	string		$application	application identifier
	 * @param	string		$controller	url controller name
	 * @return	boolean		true if controller is the application's default
	 */
	public function isDefaultController($application, $controller) {
		// lookup custom urls first
		if (isset($this->customUrls['lookup'][$application], $this->customUrls['lookup'][$application][$controller])) {
			$controller = $this->customUrls['lookup'][$application][$controller];
			if (strpos($controller, '__WCF_CMS__') !== false) {
				// remove language id component
				$controller = preg_replace('~\-\d+$~', '', $controller);
			}
		}
		
		if ($this->landingPages[$application][0] === $controller) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if currently active request represents the landing page.
	 * 
	 * @param       string[]        $classData
	 * @param       array           $metaData
	 * @return      boolean
	 */
	public function isLandingPage(array $classData, array $metaData) {
		if ($classData['className'] !== $this->landingPages['wcf'][2]) {
			return false;
		}
		
		if ($classData['className'] === CmsPage::class) {
			// check if page id matches
			if ($this->landingPages['wcf'][1] !== '__WCF_CMS__' . $metaData['cms']['pageID']) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns the class data for the active request or null if for the given
	 * configuration no proper class exist.
	 *
	 * @param	string		$application	application identifier
	 * @param	string		$controller	controller name
	 * @param	boolean		$isAcpRequest	true if this is an ACP request
	 * @param	string		$pageType	page type, e.g. 'form' or 'action'
	 * @return	string[]	className, controller and pageType
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
	
	/**
	 * Transforms a controller into its URL representation.
	 *
	 * @param	string		$controller	controller, e.g. 'BoardList'
	 * @return	string		url representation, e.g. 'board-list'
	 */
	public static function transformController($controller) {
		$parts = preg_split('~([A-Z][a-z0-9]+)~', $controller, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$parts = array_map('strtolower', $parts);
		
		return implode('-', $parts);
	}
}
