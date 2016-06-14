<?php
namespace wcf\system\application;
use wcf\data\application\Application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationList;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\request\RouteHandler;
use wcf\system\Regex;
use wcf\system\SingletonFactory;

/**
 * Handles multi-application environments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Application
 */
class ApplicationHandler extends SingletonFactory {
	/**
	 * application cache
	 * @var	Application[]
	 */
	protected $cache;
	
	/**
	 * list of page URLs
	 * @var	string[]
	 */
	protected $pageURLs = [];
	
	/**
	 * Initializes cache.
	 */
	protected function init() {
		$this->cache = ApplicationCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns an application based upon it's abbreviation. Will return the
	 * primary application if $abbreviation equals to 'wcf'
	 * 
	 * @param	string		$abbreviation	package abbreviation, e.g. `wbb` for `com.woltlab.wbb`
	 * @return	Application
	 */
	public function getApplication($abbreviation) {
		if (isset($this->cache['abbreviation'][$abbreviation])) {
			$packageID = $this->cache['abbreviation'][$abbreviation];
			
			if (isset($this->cache['application'][$packageID])) {
				return $this->cache['application'][$packageID];
			}
		}
		
		return null;
	}
	
	/**
	 * Returns an application by package id.
	 * 
	 * @param	integer		$packageID	package id
	 * @return	Application	application object
	 * @since	3.0
	 */
	public function getApplicationByID($packageID) {
		if (isset($this->cache['application'][$packageID])) {
			return $this->cache['application'][$packageID];
		}
		
		return null;
	}
	
	/**
	 * Returns pseudo-application representing WCF used for special cases,
	 * e.g. cross-domain files requestable through the webserver.
	 * 
	 * @return	Application
	 * @deprecated  3.0 please use `getApplication()` instead
	 */
	public function getWCF() {
		return $this->getApplicationByID(1);
	}
	
	/**
	 * Returns the currently active application.
	 * 
	 * @return	Application
	 */
	public function getActiveApplication() {
		// work-around during WCFSetup
		if (!PACKAGE_ID) {
			$host = str_replace(RouteHandler::getProtocol(), '', RouteHandler::getHost());
			
			return new Application(null, [
				'domainName' => $host,
				'domainPath' => RouteHandler::getPath(),
				'cookieDomain' => $host,
				'cookiePath' => RouteHandler::getPath(['acp'])
			]);
		}
		else if (isset($this->cache['application'][PACKAGE_ID])) {
			return $this->cache['application'][PACKAGE_ID];
		}
		
		return $this->getWCF();
	}
	
	/**
	 * Returns a list of dependent applications.
	 * 
	 * @return	Application[]
	 */
	public function getDependentApplications() {
		$applications = $this->getApplications();
		foreach ($applications as $key => $application) {
			if ($application->packageID == $this->getActiveApplication()->packageID) {
				unset($applications[$key]);
				break;
			}
		}
		
		return $applications;
	}
	
	/**
	 * Returns a list of all active applications.
	 * 
	 * @return	Application[]
	 */
	public function getApplications() {
		return $this->cache['application'];
	}
	
	/**
	 * Returns abbreviation for a given package id or null if application is unknown.
	 * 
	 * @param	integer		$packageID	unique package id
	 * @return	string
	 */
	public function getAbbreviation($packageID) {
		foreach ($this->cache['abbreviation'] as $abbreviation => $applicationID) {
			if ($packageID == $applicationID) {
				return $abbreviation;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns true if given $url is an internal URL.
	 * 
	 * @param	string		$url
	 * @return	boolean
	 */
	public function isInternalURL($url) {
		$protocolRegex = new Regex('^https(?=://)');
		if (empty($this->pageURLs)) {
			foreach ($this->getApplications() as $application) {
				$this->pageURLs[] = preg_replace('~/$~', '', $protocolRegex->replace($application->getPageURL(), 'http'));
			}
		}
		
		foreach ($this->pageURLs as $pageURL) {
			if (stripos($protocolRegex->replace($url, 'http'), $pageURL) === 0) {
				return true;
			}
		}
		
		// relative urls contain no protocol, including implied
		if (!preg_match('~^([a-z]+)?://~', $url)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Rebuilds cookie domain/path for all applications.
	 */
	public static function rebuild() {
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		
		$applicationAction = new ApplicationAction($applicationList->getObjects(), 'rebuild');
		$applicationAction->executeAction();
	}
}
