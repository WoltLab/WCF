<?php
namespace wcf\system\application;
use wcf\data\application\Application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationList;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Handles multi-application environments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Application
 */
class ApplicationHandler extends SingletonFactory {
	/**
	 * application cache
	 * @var	mixed[][]
	 */
	protected $cache;
	
	/**
	 * true for multi-domain setups
	 * @var boolean
	 */
	protected $isMultiDomain;
	
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
	 * primary application if the abbreviation is `wcf` or `null` if no such
	 * application exists.
	 * 
	 * @param	string		$abbreviation	package abbreviation, e.g. `wbb` for `com.woltlab.wbb`
	 * @return	Application|null
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
	 * Returns an application delivered by the package with the given id or `null`
	 * if no such application exists.
	 * 
	 * @param	integer			$packageID	package id
	 * @return	Application|null	application object
	 * @since	3.0
	 */
	public function getApplicationByID($packageID) {
		// work-around for update from 2.1 (out-dated cache)
		if ($packageID == 1 && !isset($this->cache['application'][1])) {
			$this->cache['application'][1] = new Application(1);
		}
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
			$documentRoot = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator(realpath($_SERVER['DOCUMENT_ROOT'])));
			
			// always use the core directory
			if (empty($_POST['directories']) || empty($_POST['directories']['wcf'])) {
				// within ACP
				$_POST['directories'] = ['wcf' => $documentRoot . FileUtil::removeLeadingSlash(RouteHandler::getPath(['acp']))];
			}
			
			$path = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator(FileUtil::getRelativePath($documentRoot, $_POST['directories']['wcf']))));
			
			return new Application(null, [
				'domainName' => $host,
				'domainPath' => $path,
				'cookieDomain' => $host,
			]);
		}
		
		$request = RequestHandler::getInstance()->getActiveRequest();
		if ($request !== null) {
			$abbreviation = substr($request->getClassName(), 0, mb_strpos($request->getClassName(), '\\'));
			
			return $this->getApplication($abbreviation);
		}
		
		if (isset($this->cache['application'][PACKAGE_ID])) {
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
	 * Returns abbreviation for a given package id or `null` if application is unknown.
	 * 
	 * @param	integer		$packageID	unique package id
	 * @return	string|null
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
	 * Returns the list of application abbreviations.
	 * 
	 * @return      string[]
	 * @since       3.1
	 */
	public function getAbbreviations() {
		return array_keys($this->cache['abbreviation']);
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
				$this->pageURLs[] = preg_replace('~/$~', '', $protocolRegex->replace(RouteHandler::getProtocol() . $application->domainName, 'http'));
			}
			$this->pageURLs = array_unique($this->pageURLs);
		}
		
		foreach ($this->pageURLs as $pageURL) {
			if (stripos($protocolRegex->replace($url, 'http'), $pageURL) === 0) {
				return true;
			}
		}
		
		// relative urls contain no protocol, including implied
		if (!preg_match('~^([a-zA-Z0-9]+)?://~', $url)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if this is a multi-domain setup.
	 * 
	 * @return      boolean
	 * @since       3.1
	 */
	public function isMultiDomainSetup() {
		if ($this->isMultiDomain === null) {
			$this->isMultiDomain = false;
			
			$domainName = $this->getApplicationByID(1)->domainName;
			foreach ($this->getApplications() as $application) {
				if ($application->domainName !== $domainName) {
					$this->isMultiDomain = true;
					break;
				}
			}
		}
		
		return $this->isMultiDomain;
	}
	
	/**
	 * @since 5.2
	 */
	public function rebuildActiveApplication() {
		/** @var AbstractApplication $application */
		foreach ($this->cache['application'] as $application) {
			if ($application->getPackage()->package === 'com.woltlab.wcf') {
				continue;
			}
			
			$appObject = WCF::getApplicationObject($application);
			if ($appObject instanceof AbstractApplication) {
				$appObject->rebuildActiveApplication();
			}
		}
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
	
	/**
	 * Replaces `app1_` in the given string with the correct installation number:
	 * `app{WCF_N_}`.
	 * 
	 * This method can either be used for database table names directly or for
	 * queries, for example.
	 * 
	 * @param	string		$string		string to be processed
	 * @param	bool		$skipCache	if `true`, no caches will be used and relevant application packages will be read from database directly
	 * @return	string				processed string
	 * @since	5.2
	 */
	public static function insertRealDatabaseTableNames($string, $skipCache = false) {
		if ($skipCache) {
			$packageList = new PackageList();
			$packageList->getConditionBuilder()->add('package.isApplication = ?', [1]);
			$packageList->readObjects();
			
			foreach ($packageList as $package) {
				$abbreviation = Package::getAbbreviation($package->package);
				
				$string = str_replace($abbreviation . '1_', $abbreviation . WCF_N . '_', $string);
			}
		}
		else {
			foreach (static::getInstance()->getAbbreviations() as $abbreviation) {
				$string = str_replace($abbreviation . '1_', $abbreviation . WCF_N . '_', $string);
			}
		}
		
		return $string;
	}
}
