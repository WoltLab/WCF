<?php
namespace wcf\data\application;
use wcf\data\package\Package;
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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class Application extends DatabaseObject {
	/**
	 * absolute page URL
	 * @var	string
	 */
	protected $pageURL = '';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'application';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexIsIdentity
	 */
	protected static $databaseTableIndexIsIdentity = false;
	
	/**
	 * list of all available application directories
	 * @var	array<string>
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
	 */
	public static function getDirectory($abbreviation) {
		if (static::$directories === null) {
			static::$directories = array();
			
			// read application directories
			$packageList = new PackageList();
			$packageList->getConditionBuilder()->add('package.isApplication = ?', array(1));
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
