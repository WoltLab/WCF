<?php
namespace wcf\page;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a page which fires the default event actions of a
 * page:
 *	- readParameters
 *	- readData
 *	- assignVariables
 *	- show
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
abstract class AbstractPage implements IPage {
	/**
	 * name of the active menu item
	 * @var	string
	 */
	public $activeMenuItem = '';
	
	/**
	 * value of the given action parameter
	 * @var	string
	 */
	public $action = '';
	
	/**
	 * canonical URL of this page
	 * @var	string
	 */
	public $canonicalURL = '';
	
	/**
	 * is true if canonical URL will be enforced even if POST data is represent
	 * @var	boolean
	 */
	public $forceCanonicalURL = false;
	
	/**
	 * indicates if you need to be logged in to access this page
	 * @var	boolean
	 */
	public $loginRequired = false;
	
	/**
	 * needed modules to view this page
	 * @var	string[]
	 */
	public $neededModules = [];
	
	/**
	 * needed permissions to view this page
	 * @var	string[]
	 */
	public $neededPermissions = [];
	
	/**
	 * name of the template for the called page
	 * @var	string
	 */
	public $templateName = '';
	
	/**
	 * abbreviation of the application the template belongs to
	 * @var	string
	 */
	public $templateNameApplication = '';
	
	/**
	 * enables template usage
	 * @var	string
	 */
	public $useTemplate = true;
	
	/**
	 * @inheritDoc
	 */
	public final function __construct() { }
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		// call default methods
		$this->readParameters();
		$this->show();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		// call readParameters event
		EventHandler::getInstance()->fireAction($this, 'readParameters');
		
		// read action parameter
		if (isset($_REQUEST['action'])) $this->action = $_REQUEST['action'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		// call readData event
		EventHandler::getInstance()->fireAction($this, 'readData');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		// call assignVariables event
		EventHandler::getInstance()->fireAction($this, 'assignVariables');
		
		// assign parameters
		WCF::getTPL()->assign([
			'action' => $this->action,
			'templateName' => $this->templateName,
			'canonicalURL' => $this->canonicalURL
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkModules() {
		// call checkModules event
		EventHandler::getInstance()->fireAction($this, 'checkModules');
		
		// check modules
		foreach ($this->neededModules as $module) {
			if (!defined($module) || !constant($module)) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		// call checkPermissions event
		EventHandler::getInstance()->fireAction($this, 'checkPermissions');
		
		// check permission, it is sufficient to have at least one permission
		if (!empty($this->neededPermissions)) {
			$hasPermissions = false;
			foreach ($this->neededPermissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermissions = true;
					break;
				}
			}
			
			if (!$hasPermissions) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check if active user is logged in
		if ($this->loginRequired && !WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// check if current request URL matches the canonical URL
		if ($this->canonicalURL && (empty($_POST) || $this->forceCanonicalURL)) {
			$canoncialURL = parse_url(preg_replace('~[?&]s=[a-f0-9]{40}~', '', $this->canonicalURL));
			
			// use $_SERVER['REQUEST_URI'] because it represents the URL used to access the site and not the internally rewritten one
			// IIS Rewrite-Module has a bug causing the REQUEST_URI to be ISO-encoded
			$requestURI = (!empty($_SERVER['UNENCODED_URL'])) ? $_SERVER['UNENCODED_URL'] : $_SERVER['REQUEST_URI'];
			$requestURI = preg_replace('~[?&]s=[a-f0-9]{40}~', '', $requestURI);
			
			if (!StringUtil::isUTF8($requestURI)) {
				$requestURI = StringUtil::convertEncoding('ISO-8859-1', 'UTF-8', $requestURI);
			}
			
			// some webservers output lower-case encoding (e.g. %c3 instead of %C3)
			$requestURI = preg_replace_callback('~%(?P<encoded>[a-zA-Z0-9]{2})~', function($matches) {
				return '%' . strtoupper($matches['encoded']);
			}, $requestURI);
			
			$requestURL = parse_url($requestURI);
			
			$redirect = false;
			if ($canoncialURL['path'] != $requestURL['path']) {
				$redirect = true;
			}
			else if (isset($canoncialURL['query'])) {
				if (!isset($requestURL['query'])) {
					$redirect = true;
				}
				else {
					parse_str($canoncialURL['query'], $cQueryString);
					parse_str($requestURL['query'], $rQueryString);
					
					foreach ($cQueryString as $key => $value) {
						if (!isset($rQueryString[$key]) || $rQueryString[$key] != $value) {
							$redirect = true;
							break;
						}
					}
				}
			}
			
			if ($redirect) {
				$redirectURL = $this->canonicalURL;
				// TODO
				/*
				if (!empty($requestURL['query'])) {
					$queryString = $requestURL['query'];
					parse_str($requestURL['query'], $rQueryString);
					
					if (!empty($canoncialURL['query'])) {
						parse_str($canoncialURL['query'], $cQueryString);
						
						// clean query string
						foreach ($cQueryString as $key => $value) {
							if (isset($rQueryString[$key])) {
								unset($rQueryString[$key]);
							}
						}
					}
					
					// drop route data from query
					if (!URL_LEGACY_MODE) {
						foreach ($rQueryString as $key => $value) {
							if ($value === '') {
								unset($rQueryString[$key]);
							}
						}
					}
					
					if (!empty($rQueryString)) {
						$redirectURL .= (mb_strpos($redirectURL, '?') === false ? '?' : '&') . http_build_query($rQueryString, '', '&');
					}
				}
				*/
				
				// force a permanent redirect as recommended by Google
				// https://support.google.com/webmasters/answer/6033086?hl=en#a_note_about_redirects
				@header('HTTP/1.0 301 Moved Permanently');
				HeaderUtil::redirect($redirectURL, false);
				exit;
			}
		}
		
		// sets the active menu item
		$this->setActiveMenuItem();
		
		// check modules
		$this->checkModules();
		
		// check permission
		$this->checkPermissions();
		
		// read data
		$this->readData();
		
		// assign variables
		$this->assignVariables();
		
		// call show event
		EventHandler::getInstance()->fireAction($this, 'show');
		
		// try to guess template name
		$classParts = explode('\\', get_class($this));
		if (empty($this->templateName)) {
			$className = preg_replace('~(Form|Page)$~', '', array_pop($classParts));
			
			// check if this an *Edit page and use the add-template instead
			if (substr($className, -4) == 'Edit') {
				$className = substr($className, 0, -4) . 'Add';
			}
			
			$this->templateName = lcfirst($className);
			
			// assign guessed template name
			WCF::getTPL()->assign('templateName', $this->templateName);
		}
		if (empty($this->templateNameApplication)) {
			$this->templateNameApplication = array_shift($classParts);
			
			// assign guessed template application
			WCF::getTPL()->assign('templateNameApplication', $this->templateNameApplication);
		}
		
		if ($this->useTemplate) {
			// show template
			WCF::getTPL()->display($this->templateName, $this->templateNameApplication);
		}
	}
	
	/**
	 * Sets the active menu item of the page.
	 */
	protected function setActiveMenuItem() {
		if (!empty($this->activeMenuItem)) {
			if (RequestHandler::getInstance()->isACPRequest()) {
				ACPMenu::getInstance()->setActiveMenuItem($this->activeMenuItem);
			}
		}
	}
}
