<?php
namespace wcf\data\application;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\package\PackageList;
use wcf\data\DatabaseObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\RouteHandler;
use wcf\util\FileUtil;

/**
 * Represents an application.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Application
 *
 * @property-read	integer		$packageID	id of the package which delivers the application
 * @property-read	string		$domainName	domain used to access the application (may not contain path components, see `$domainPath`)
 * @property-read	string		$domainPath	path used to access the application
 * @property-read	string		$cookieDomain	domain used to set cookies (corresponds to `domain` cookie property; may not contain path components)
 * @property-read	integer		$isTainted	is `1` if the application is being uninstalled and thus should not be loaded during uninstallation, otherwise `0`
 * @property-read       integer         $landingPageID  id of the page that is used as initial page when app is accessed without a controller name
 */
class Application extends DatabaseObject {
	/**
	 * related package object
	 * @var	Package
	 */
	protected $package;
	
	/**
	 * absolute page URL
	 * @var	string
	 */
	protected $pageURL = '';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'packageID';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexIsIdentity = false;
	
	/**
	 * list of all available application directories
	 * @var	string[]
	 */
	protected static $directories = null;
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		if (isset($data['domainPath'])) {
			// The leading and trailing slashes are required and enforced through the admin panel,
			// however, some users edit the database directly and omit them, causing incorrect urls.
			$data['domainPath'] = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash($data['domainPath']));
		}
		
		parent::handleData($data);
	}
	
	/**
	 * Returns the abbreviation of the application.
	 * 
	 * @return	string
	 */
	public function getAbbreviation() {
		return ApplicationHandler::getInstance()->getAbbreviation($this->packageID);
	}
	
	/**
	 * Returns related package object.
	 * 
	 * @return	Package		related package object
	 */
	public function getPackage() {
		if ($this->package === null) {
			$this->package = PackageCache::getInstance()->getPackage($this->packageID);
		}
		
		return $this->package;
	}
	
	/**
	 * Returns absolute page URL.
	 * 
	 * @return	string
	 */
	public function getPageURL() {
		if (empty($this->pageURL)) {
			$this->pageURL = RouteHandler::getProtocol() . $this->domainName . $this->domainPath;
		}
		
		return $this->pageURL;
	}
	
	/**
	 * Returns the directory of the application with the given abbreviation.
	 * 
	 * @param	string		$abbreviation
	 * @return	string
	 * @throws	SystemException
	 */
	public static function getDirectory($abbreviation) {
		if (static::$directories === null) {
			static::$directories = [];
			
			// read application directories
			$packageList = new PackageList();
			$packageList->getConditionBuilder()->add('package.isApplication = ?', [1]);
			$packageList->readObjects();
			foreach ($packageList as $package) {
				$abbr = Package::getAbbreviation($package->package);
				static::$directories[$abbr] = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$package->packageDir));
			}
		}
		
		if (!isset(static::$directories[$abbreviation])) {
			throw new SystemException("Unknown application '".$abbreviation."'");
		}
		
		return static::$directories[$abbreviation];
	}
}
