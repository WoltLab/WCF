<?php
namespace wcf\data\devtools\project;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\data\package\Package;
use wcf\data\DatabaseObject;
use wcf\data\package\PackageList;
use wcf\system\devtools\package\DevtoolsPackageArchive;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Represents a devtools project.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Devtools\Project
 * @since	3.1
 * 
 * @property-read	integer		$projectID	unique id of the project
 * @property-read	string		$name		internal name for display inside the ACP
 * @property-read	string		$path		file system path
 */
class DevtoolsProject extends DatabaseObject {
	/**
	 * is `true` if it has already been attempted to fetch a package
	 * @var		bool
	 * @since	5.2
	 */
	protected $didFetchPackage = false;
	
	/**
	 * @var boolean
	 */
	protected $isCore;
	
	/**
	 * @var Package
	 */
	protected $package;
	
	/**
	 * @var DevtoolsPackageArchive
	 */
	protected $packageArchive;
	
	/**
	 * Returns a list of decorated PIPs.
	 * 
	 * @return      DevtoolsPip[]
	 */
	public function getPips() {
		$pipList = new PackageInstallationPluginList();
		$pipList->sqlOrderBy = 'pluginName';
		$pipList->readObjects();
		
		$pips = [];
		foreach ($pipList as $pip) {
			$pip = new DevtoolsPip($pip);
			$pip->setProject($this);
			
			$pips[] = $pip;
		}
		
		return $pips;
	}
	
	/**
	 * Validates the repository and returns the first error message, or
	 * an empty string on success.
	 * 
	 * @return      string
	 */
	public function validate() {
		$errorType = self::validatePath($this->path);
		if ($errorType !== '') {
			return WCF::getLanguage()->get('wcf.acp.devtools.project.path.error.' . $errorType);
		}
		
		return $this->validatePackageXml();
	}
	
	/**
	 * Returns true if this project appears to be `WoltLab Suite Core`.
	 * 
	 * @return      boolean
	 */
	public function isCore() {
		if ($this->isCore === null) {
			$this->isCore = self::pathIsCore($this->path);
		}
		
		return $this->isCore;
	}
	
	/**
	 * Returns the path to the project's `package.xml` file.
	 * 
	 * @return	string
	 * @since	5.2
	 */
	public function getPackageXmlPath() {
		return $this->path . ($this->isCore() ? 'com.woltlab.wcf/' : '') . 'package.xml';
	}
	
	/**
	 * Validates the package.xml and checks if the package is already installed.
	 * 
	 * @return      string
	 */
	public function validatePackageXml() {
		$packageXml = $this->getPackageXmlPath();
		$this->packageArchive = new DevtoolsPackageArchive($packageXml);
		try {
			$this->packageArchive->openArchive();
		}
		catch (PackageValidationException $e) {
			return $e->getErrorMessage();
		}
		
		if ($this->getPackage() === null) {
			return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.notInstalled', [
				'project' => $this
			]);
		}
		
		$normalizeVersion = function($version) {
			return preg_replace('~^(\d+)\.(\d+)\..*$~', '\\1.\\2', $version);
		};
		
		if ($normalizeVersion($this->packageArchive->getPackageInfo('version')) !== $normalizeVersion($this->package->packageVersion)) {
			return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.versionMismatch', [
				'version' => $this->packageArchive->getPackageInfo('version'),
				'packageVersion' => $this->package->packageVersion
			]);
		}
		
		if (!$this->isCore()) {
			$compatibleVersions = $this->packageArchive->getCompatibleVersions();
			if (empty($compatibleVersions)) {
				return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.missingCompatibility');
			}
			$isCompatible = $isOlderVersion = false;
			foreach ($compatibleVersions as $version) {
				if (WCF::isSupportedApiVersion($version)) {
					$isCompatible = true;
					break;
				}
				else if ($version < WSC_API_VERSION) {
					$isOlderVersion = true;
				}
			}
			
			if (!$isCompatible) {
				return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.unsupportedCompatibility', ['isOlderVersion' => $isOlderVersion]);
			}
		}
		
		return '';
	}
	
	/**
	 * @return      Package
	 */
	public function getPackage() {
		if ($this->package === null) {
			$packageList = new PackageList();
			$packageList->getConditionBuilder()->add('package = ?', [$this->getPackageArchive()->getPackageInfo('name')]);
			$packageList->readObjects();
			
			if (count($packageList)) {
				$this->package = $packageList->current();
			}
			
			$this->didFetchPackage = true;
		}
		
		return $this->package;
	}
	
	/**
	 * @return      DevtoolsPackageArchive
	 */
	public function getPackageArchive() {
		if ($this->packageArchive === null) {
			$this->packageArchive = new DevtoolsPackageArchive($this->path . ($this->isCore() ? 'com.woltlab.wcf/' : '') . 'package.xml');
			
			try {
				$this->packageArchive->openArchive();
			}
			catch (PackageValidationException $e) {
				// we do not care for errors here, `validatePackageXml()`
				// takes care of that
			}
		}
		
		return $this->packageArchive;
	}
	
	/**
	 * Returns the absolute paths of the language files.
	 * 
	 * @return	string[]
	 */
	public function getLanguageFiles() {
		$languageDirectory = $this->path . ($this->isCore() ? 'wcfsetup/install/lang/' : 'language/');
		
		if (!is_dir($languageDirectory)) {
			return [];
		}
		
		return array_values(DirectoryUtil::getInstance($languageDirectory)->getFiles(SORT_ASC, Regex::compile('\w+\.xml')));
	}
	
	/**
	 * Sets the package that belongs to this project.
	 * 
	 * @param	Package		$package
	 * @throws	\InvalidArgumentException	if the identifier of the given package does not match
	 * @since	5.2
	 */
	public function setPackage(Package $package) {
		if ($package->package !== $this->getPackageArchive()->getPackageInfo('name')) {
			throw new \InvalidArgumentException("Package identifier of given package ('{$package->package}') does not match ('{$this->packageArchive->getPackageInfo('name')}')");
		}
		
		$this->package = $package;
	}
	
	/**
	 * Validates the provided path and returns an error code
	 * if the path does not exist (`notFound`) or if there is
	 * no package.xml (`packageXml`).
	 * 
	 * @param       string          $path
	 * @return      string
	 */
	public static function validatePath($path) {
		if (!is_dir($path)) {
			return 'notFound';
		}
		else if (!file_exists($path . 'package.xml')) {
			// check if this is `com.woltlab.wcf`
			if (!self::pathIsCore($path)) {
				return 'packageXml';
			}
		}
		
		return '';
	}
	
	/**
	 * Returns true if the path appears to point to `WoltLab Suite Core`.
	 * 
	 * @param       string          $path
	 * @return      boolean
	 */
	public static function pathIsCore($path) {
		return (is_dir($path . 'com.woltlab.wcf') && file_exists($path . 'com.woltlab.wcf/package.xml'));
	}
}
