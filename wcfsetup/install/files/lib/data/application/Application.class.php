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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Application
 *
 * @property-read	integer		$packageID
 * @property-read	string		$domainName
 * @property-read	string		$domainPath
 * @property-read	string		$cookieDomain
 * @property-read	string		$cookiePath
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
	protected static $databaseTableName = 'application';
	
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
	 * Returns the directory of the application with the given abbrevation.
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
