<?php
namespace wcf\data\devtools\project;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObject;
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
 * @copyright	2001-2018 WoltLab GmbH
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
	 * Validates the package.xml and checks if the package is already installed.
	 * 
	 * @return      string
	 */
	public function validatePackageXml() {
		$packageXml = $this->path . ($this->isCore() ? 'com.woltlab.wcf/' : '') . 'package.xml';
		$this->packageArchive = new DevtoolsPackageArchive($packageXml);
		try {
			$this->packageArchive->openArchive();
		}
		catch (PackageValidationException $e) {
			return $e->getErrorMessage();
		}
		
		$this->package = PackageCache::getInstance()->getPackageByIdentifier($this->packageArchive->getPackageInfo('name'));
		if ($this->package === null) {
			return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.notInstalled', [
				'package' => $this->packageArchive->getPackageInfo('name')
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
		return $this->package;
	}
	
	/**
	 * @return      DevtoolsPackageArchive
	 */
	public function getPackageArchive() {
		return $this->packageArchive;
	}
	
	/**
	 * Returns the absolute paths of the language files.
	 * 
	 * @return	string[]
	 */
	public function getLanguageFiles() {
		$languageDirectory = $this->path . ($this->isCore() ? 'wcfsetup/install/lang/' : 'language/');
		
		return array_values(DirectoryUtil::getInstance($languageDirectory)->getFiles(SORT_ASC, Regex::compile('\w+\.xml')));
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
