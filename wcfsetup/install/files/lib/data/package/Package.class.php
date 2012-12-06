<?php
namespace wcf\data\package;
use wcf\data\DatabaseObject;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a package.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 */
class Package extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageID';
	
	/**
	 * list of packages that this package requires
	 * @var	array<wcf\data\package\Package>
	 */
	protected $dependencies = null;
	
	/**
	 * list of packages that require this package
	 * @var	array<wcf\data\package\Package>
	 */
	protected $dependentPackages = null;
	
	/**
	 * installation directory
	 * @var	string
	 */
	protected $dir = '';
	
	/**
	 * list of packages that were given as required packages during installation
	 * @var	array<wcf\data\package\Package>
	 */
	protected $requiredPackages = null;
	
	/**
	 * Returns true, if this package is required by other packages.
	 * 
	 * @return	boolean
	 */
	public function isRequired() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_requirement
			WHERE	requirement = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->packageID));
		$row = $statement->fetchArray();
		
		return $row['count'];
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
		return WCF::getLanguage()->get($this->instanceName ?: $this->packageName);
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
	 * Returns a list of all by this package required packages.
	 * Contains required packages and the requirements of the required packages.
	 * 
	 * @return	array<wcf\data\package\Package>
	 */
	public function getDependencies() {
		if ($this->dependencies === null) {
			throw new SystemException("Package::getDependencies()");
		}
		
		return $this->dependencies;
	}
	
	/**
	 * Returns a list of all packages that require this package.
	 * Returns packages that require this package and packages that require these packages.
	 * 
	 * @return	array<wcf\data\package\Package>
	 */
	public function getDependentPackages() {
		if ($this->dependentPackages === null) {
			$this->dependentPackages = array();
			
			$sql = "SELECT		package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package_requirement package_requirement
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_requirement.packageID)
				WHERE		package_requirement.requirement = ?
				ORDER BY	packageName ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->packageID));
			while ($package = $statement->fetchObject('wcf\data\package\Package')) {
				$this->dependentPackages[$package->packageID] = $package;
			}
		}
		
		return $this->dependentPackages;
	}
	
	/**
	 * Returns a list of the requirements of this package.
	 * Contains the content of the <requiredPackages> tag in the package.xml of this package.
	 * 
	 * @return	array<wcf\data\package\Package>
	 */
	public function getRequiredPackages() {
		if ($this->requiredPackages === null) {
			$this->requiredPackages = array();
			
			$sql = "SELECT		package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package_requirement package_requirement
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_requirement.requirement)
				WHERE		package_requirement.packageID = ?
				ORDER BY	packageName ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->packageID));
			while ($package = $statement->fetchObject('wcf\data\package\Package')) {
				$this->requiredPackages[$package->packageID] = $package;
			}
		}
		
		return $this->requiredPackages;
	}
	
	/**
	 * Checks if a package name is valid.
	 * 
	 * A valid package name begins with at least one alphanumeric character
	 * or an underscore, followed by a dot, followed by at least one alphanumeric
	 * character or an underscore and the same again, possibly repeatedly.
	 * Example: 'com.woltlab.wcf'.
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
	 * Returns true, if package version is valid.
	 * 
	 * Exmaples of valid package versions:
	 *	1.0.0 pl 3
	 *	4.0.0 Alpha 1
	 *	3.1.7 rC 4
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
	 * version 1.2.0, all releases from 1.1.0 to  1.1.9 may be updated using
	 * this package.
	 * 
	 * @param	string		$currentVersion
	 * @param	string		$fromVersion
	 * @return	boolean
	 */
	public static function checkFromversion($currentVersion, $fromversion) {
		if (StringUtil::indexOf($fromversion, '*') !== false) {
			// from version with wildcard
			// use regular expression
			$fromversion = StringUtil::replace('\*', '.*', preg_quote($fromversion, '!'));
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
	 * Rebuilds the requirement map for the given package id.
	 * 
	 * @param	integer		$packageID
	 */
	public static function rebuildPackageRequirementMap($packageID) {
		// delete old entries
		$sql = "DELETE FROM	wcf".WCF_N."_package_requirement_map
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		
		// fetch requirements of requirements
		$requirements = array();
		$sql = "SELECT		requirement, level
			FROM		wcf".WCF_N."_package_requirement_map
			WHERE		packageID IN (
						SELECT	requirement
						FROM	wcf".WCF_N."_package_requirement
						WHERE	packageID = ?
					)
			ORDER BY	level ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		while ($row = $statement->fetchArray()) {
			// use reverse order, highest level epic wins
			$requirements[$row['requirement']] = $row['level'];
		}
		
		// insert requirements of requirements
		if (!empty($requirements)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_requirement_map
						(packageID, requirement, level)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($requirements as $requirement => $level) {
				$statement->execute(array($packageID, $requirement, $level));
			}
		}
		
		// fetch requirements
		$directRequirements = array();
		$conditions = new PreparedStatementConditionBuilder($sql);
		$conditions->add("packageID = ?", array($packageID));
		if (!empty($requirements)) {
			$conditions->add("requirement NOT IN (?)", array(array_keys($requirements)));
		}
		
		$sql = "SELECT	requirement, 
				(
					SELECT	MAX(level) AS requirementLevel
					FROM	wcf".WCF_N."_package_requirement_map
					WHERE	packageID = package_requirement.requirement
				) AS requirementLevel
			FROM	wcf".WCF_N."_package_requirement package_requirement
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$row['requirementLevel'] = intval($row['requirementLevel']) + 1;
			$directRequirements[$row['requirement']] = $row['requirementLevel'];
		}
		
		// insert requirements
		if (!empty($directRequirements)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_requirement_map
						(packageID, requirement, level)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($directRequirements as $requirement => $level) {
				$statement->execute(array($packageID, $requirement, $level));
			}
		}
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
		$prefix = strtoupper(Package::getAbbreviation($package->package));
		
		$file->write("// ".$package->package." (packageID ".$package->packageID.")\n");
		$file->write("if (!defined('".$prefix."_DIR')) define('".$prefix."_DIR', dirname(__FILE__).'/');\n");
		$file->write("if (!defined('RELATIVE_".$prefix."_DIR')) define('RELATIVE_".$prefix."_DIR', '');\n");
		$file->write("if (!defined('".$prefix."_N')) define('".$prefix."_N', '".WCF_N."_".$package->instanceNo."');\n");
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
	
	/**
	 * Returns a list of plugins for currently active application.
	 * 
	 * @todo	Care about simple plugins just providing some crap.
	 * @return	wcf\data\package\PackageList
	 */
	public static function getPluginList() {
		$pluginList = new PackageList();
		$pluginList->getConditionBuilder()->add("package.isApplication = ?", array(0));
		
		return $pluginList;
	}
}
