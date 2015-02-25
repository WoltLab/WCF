<?php
namespace wcf\data\package;
use wcf\data\DatabaseObject;
use wcf\system\io\File;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents a package.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 */
class Package extends DatabaseObject {
	/**
	 * list of packages that this package requires
	 * @var	array<\wcf\data\package\Package>
	 */
	protected $dependencies = null;
	
	/**
	 * list of packages that require this package
	 * @var	array<\wcf\data\package\Package>
	 */
	protected $dependentPackages = null;
	
	/**
	 * installation directory
	 * @var	string
	 */
	protected $dir = '';
	
	/**
	 * list of packages that were given as required packages during installation
	 * @var	array<\wcf\data\package\Package>
	 */
	protected $requiredPackages = null;
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageID';
	
	/**
	 * list of ids of packages which are required by another package
	 * @var	array<integer>
	 */
	protected static $requiredPackageIDs = null;
	
	/**
	 * package requirements
	 * @var	array
	 */
	protected static $requirements = null;
	
	/**
	 * Returns true if this package is required by other packages.
	 * 
	 * @return	boolean
	 */
	public function isRequired() {
		self::loadRequirements();
		
		return in_array($this->packageID, self::$requiredPackageIDs);
	}
	
	/**
	 * Returns true if package is a plugin.
	 * 
	 * @return	boolean
	 */
	public function isPlugin() {
		if ($this->isApplication) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the name of this package.
	 * 
	 * @return	string
	 */
	public function getName() {
		return WCF::getLanguage()->get($this->packageName);
	}
	
	/**
	 * @see	\wcf\data\package\Package::getName()
	 */
	public function __toString() {
		return $this->getName();
	}
	
	/**
	 * Returns the abbreviation of the package name.
	 * 
	 * @param	string		$package
	 * @return	string
	 */
	public static function getAbbreviation($package) {
		$array = explode('.', $package);
		return array_pop($array);
	}
	
	/**
	 * Returns the list of packages which are required by this package. The
	 * returned packages are the packages given in the <requiredpackages> tag
	 * in the package.xml of this package.
	 * 
	 * @return	array<\wcf\data\package\Package>
	 */
	public function getRequiredPackages() {
		if ($this->requiredPackages === null) {
			self::loadRequirements();
			
			$this->requiredPackages = array();
			if (isset(self::$requirements[$this->packageID])) {
				foreach (self::$requirements[$this->packageID] as $packageID) {
					$this->requiredPackages[$packageID] = PackageCache::getInstance()->getPackage($packageID);
				}
			}
		}
		
		return $this->requiredPackages;
	}
	
	/**
	 * Returns true if current user can uninstall this package.
	 * 
	 * @return	boolean
	 */
	public function canUninstall() {
		if (!WCF::getSession()->getPermission('admin.system.package.canUninstallPackage')) {
			return false;
		}
		
		// disallow uninstallation of WCF
		if ($this->package == 'com.woltlab.wcf') {
			return false;
		}
		
		// check if package is required by another package
		if (self::isRequired($this->packageID)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a list of packages dependent from current package.
	 * 
	 * @return	array<\wcf\data\package\Package>
	 */
	public function getDependentPackages() {
		if ($this->dependentPackages === null) {
			self::loadRequirements();
			
			$this->dependentPackages = array();
			foreach (self::$requirements as $packageID => $requiredPackageIDs) {
				if (in_array($this->packageID, $requiredPackageIDs)) {
					$this->dependentPackages[$packageID] = PackageCache::getInstance()->getPackage($packageID);
				}
			}
		}
		
		return $this->dependentPackages;
	}
	
	/**
	 * Overwrites current package version.
	 * 
	 * DO NOT call this method outside the package installation!
	 * 
	 * @param	string		$packageVersion
	 */
	public function setPackageVersion($packageVersion) {
		$this->data['packageVersion'] = $packageVersion;
	}
	
	/**
	 * Loads package requirements.
	 */
	protected static function loadRequirements() {
		if (self::$requirements === null) {
			$sql = "SELECT	packageID, requirement
				FROM	wcf".WCF_N."_package_requirement";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			self::$requiredPackageIDs = array();
			self::$requirements = array();
			while ($row = $statement->fetchArray()) {
				if (!isset(self::$requirements[$row['packageID']])) {
					self::$requirements[$row['packageID']] = array();
				}
				
				self::$requirements[$row['packageID']][] = $row['requirement'];
				
				if (!in_array($row['requirement'], self::$requiredPackageIDs)) {
					self::$requiredPackageIDs[] = $row['requirement'];
				}
			}
		}
	}
	
	/**
	 * Returns true if package identified by $package is already installed.
	 * 
	 * @param	string		$package
	 * @return	boolean
	 */
	public static function isAlreadyInstalled($package) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package
			WHERE	package = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($package));
		$row = $statement->fetchArray();
		
		return ($row['count'] ? true : false);
	}
	
	/**
	 * Checks if a package name is valid.
	 * 
	 * A valid package name begins with at least one alphanumeric character
	 * or an underscore, followed by a dot, followed by at least one alphanumeric
	 * character or an underscore and the same again, possibly repeatedly.
	 * Example:
	 * 	com.woltlab.wcf
	 * 
	 * Reminder: The package name being examined here contains the 'name' attribute
	 * of the 'package' tag noted in the 'packages.xml' file delivered inside
	 * the respective package.
	 * 
	 * @param	string		$packageName
	 * @return	boolean		isValid
	 */
	public static function isValidPackageName($packageName) {
		return preg_match('%^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$%', $packageName);
	}
	
	/**
	 * Returns true if package version is valid.
	 * 
	 * Examples of valid package versions:
	 * 	1.0.0 pl 3
	 * 	4.0.0 Alpha 1
	 * 	3.1.7 rC 4
	 * 
	 * @param	string		$version
	 * @return	boolean
	 */
	public static function isValidVersion($version) {
		return preg_match('~^([0-9]+)\.([0-9]+)\.([0-9]+)(\ (a|alpha|b|beta|d|dev|rc|pl)\ ([0-9]+))?$~is', $version);
	}
	
	/**
	 * Checks the version number of the installed package against the "fromversion"
	 * number of the update.
	 * 
	 * The "fromversion" number may contain wildcards (asterisks) which means
	 * that the update covers the whole range of release numbers where the asterisk
	 * wildcards digits from 0 to 9.
	 * For example, if "fromversion" is "1.1.*" and this package updates to
	 * version 1.2.0, all releases from 1.1.0 to 1.1.9 may be updated using
	 * this package.
	 * 
	 * @param	string		$currentVersion
	 * @param	string		$fromVersion
	 * @return	boolean
	 */
	public static function checkFromversion($currentVersion, $fromversion) {
		if (mb_strpos($fromversion, '*') !== false) {
			// from version with wildcard
			// use regular expression
			$fromversion = str_replace('\*', '.*', preg_quote($fromversion, '!'));
			if (preg_match('!^'.$fromversion.'$!i', $currentVersion)) {
				return true;
			}
		}
		else {
			if (self::compareVersion($currentVersion, $fromversion, '=')) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Compares two version number strings.
	 * 
	 * @param	string		$version1
	 * @param	string		$version2
	 * @param	string		$operator
	 * @return	boolean		result
	 * @see	http://www.php.net/manual/en/function.version-compare.php
	 */
	public static function compareVersion($version1, $version2, $operator = null) {
		$version1 = self::formatVersionForCompare($version1);
		$version2 = self::formatVersionForCompare($version2);
		if ($operator === null) return version_compare($version1, $version2);
		else return version_compare($version1, $version2, $operator);
	}
	
	/**
	 * Formats a package version string for comparing.
	 * 
	 * @param	string		$version
	 * @return	string		formatted version
	 * @see		http://www.php.net/manual/en/function.version-compare.php
	 */
	private static function formatVersionForCompare($version) {
		// remove spaces
		$version = str_replace(' ', '', $version);
		
		// correct special version strings
		$version = str_ireplace('dev', 'dev', $version);
		$version = str_ireplace('alpha', 'alpha', $version);
		$version = str_ireplace('beta', 'beta', $version);
		$version = str_ireplace('RC', 'RC', $version);
		$version = str_ireplace('pl', 'pl', $version);
		
		return $version;
	}
	
	/**
	 * Writes the config.inc.php for an application.
	 * 
	 * @param	integer		$packageID
	 */
	public static function writeConfigFile($packageID) {
		$package = new Package($packageID);
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$package->packageDir));
		$file = new File($packageDir.PackageInstallationDispatcher::CONFIG_FILE);
		$file->write("<?php\n");
		$prefix = strtoupper(self::getAbbreviation($package->package));
		
		$file->write("// ".$package->package." (packageID ".$package->packageID.")\n");
		$file->write("if (!defined('".$prefix."_DIR')) define('".$prefix."_DIR', dirname(__FILE__).'/');\n");
		$file->write("if (!defined('RELATIVE_".$prefix."_DIR')) define('RELATIVE_".$prefix."_DIR', '');\n");
		$file->write("\n");
		
		// write general information
		$file->write("// general info\n");
		$file->write("if (!defined('RELATIVE_WCF_DIR')) define('RELATIVE_WCF_DIR', RELATIVE_".$prefix."_DIR.'".FileUtil::getRelativePath($packageDir, WCF_DIR)."');\n");
		$file->write("if (!defined('PACKAGE_ID')) define('PACKAGE_ID', ".$packageID.");\n");
		$file->write("if (!defined('PACKAGE_NAME')) define('PACKAGE_NAME', '".str_replace("'", "\'", $package->getName())."');\n");
		$file->write("if (!defined('PACKAGE_VERSION')) define('PACKAGE_VERSION', '".$package->packageVersion."');\n");
		
		// write end
		$file->close();
	}
}
