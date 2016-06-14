<?php
namespace wcf\system\package\validation;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\data\package\Package;
use wcf\system\package\PackageArchive;
use wcf\system\SingletonFactory;

/**
 * Manages recursive validation of package archives.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Validation
 */
class PackageValidationManager extends SingletonFactory {
	/**
	 * list of known package installation plugins
	 * @var	string[]
	 */
	protected $packageInstallationPlugins = [];
	
	/**
	 * package validation archive object
	 * @var	\wcf\system\package\validation\PackageValidationArchive
	 */
	protected $packageValidationArchive = null;
	
	/**
	 * virtual package list containing package => packageVersion
	 * @var	string[]
	 */
	protected $virtualPackageList = [];
	
	/**
	 * validation will only check if the primary package looks like it can be installed or updated
	 * @var	integer
	 */
	const VALIDATION_WEAK = 0;
	
	/**
	 * validation will recursively check dependencies
	 * @var	integer
	 */
	const VALIDATION_RECURSIVE = 1;
	
	/**
	 * validation will use the previously gathered exclusions and check them
	 * @var	integer
	 */
	const VALIDATION_EXCLUSION = 2;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$pipList = new PackageInstallationPluginList();
		$pipList->readObjects();
		foreach ($pipList as $pip) {
			$this->packageInstallationPlugins[$pip->pluginName] = $pip->className;
		}
	}
	
	/**
	 * Validates given archive for existance and ability to be installed/updated. If you set the
	 * second parameter $deepInspection to "false", the system will only check if the archive
	 * looks fine, this is useful for a rough check during upload when a more detailed check will
	 * be performed afterwards.
	 * 
	 * @param	string		$archive
	 * @param	boolean		$deepInspection
	 * @return	boolean
	 */
	public function validate($archive, $deepInspection) {
		$this->virtualPackageList = [];
		$this->packageValidationArchive = new PackageValidationArchive($archive);
		
		if ($deepInspection) {
			if (!$this->packageValidationArchive->validate(self::VALIDATION_RECURSIVE)) {
				return false;
			}
			
			return $this->packageValidationArchive->validate(self::VALIDATION_EXCLUSION);
		}
		
		return $this->packageValidationArchive->validate(self::VALIDATION_WEAK);
	}
	
	/**
	 * Returns package validation archive object.
	 * 
	 * @return	\wcf\system\package\validation\PackageValidationArchive
	 */
	public function getPackageValidationArchive() {
		return $this->packageValidationArchive;
	}
	
	/**
	 * Adds a virtual package with the corresponding version, if the package is already known,
	 * the higher version number will be stored.
	 * 
	 * @param	string		$package
	 * @param	string		$packageVersion
	 * @return	boolean
	 */
	public function addVirtualPackage($package, $packageVersion) {
		if (isset($this->virtualPackageList[$package])) {
			if (Package::compareVersion($packageVersion, $this->virtualPackageList[$package], '<')) {
				return false;
			}
		}
		
		$this->virtualPackageList[$package] = $packageVersion;
		
		return true;
	}
	
	/**
	 * Returns the version number of a virtual package or null if it doesn't exist.
	 * 
	 * @param	string		$package
	 * @return	string
	 */
	public function getVirtualPackage($package) {
		if (isset($this->virtualPackageList[$package])) {
			return $this->virtualPackageList[$package];
		}
		
		return null;
	}
	
	/**
	 * Returns the iteratable package archive list.
	 * 
	 * @return	\RecursiveIteratorIterator
	 */
	public function getPackageValidationArchiveList() {
		$packageValidationArchive = new PackageValidationArchive('');
		$packageValidationArchive->setChildren([$this->packageValidationArchive]);
		
		return new \RecursiveIteratorIterator($packageValidationArchive, \RecursiveIteratorIterator::SELF_FIRST);
	}
	
	/**
	 * Recursively traverses the package validation archives and returns the first exception message.
	 * 
	 * @return	string
	 */
	public function getExceptionMessage() {
		foreach ($this->getPackageValidationArchiveList() as $packageArchive) {
			if ($packageArchive->getExceptionMessage()) {
				return $packageArchive->getExceptionMessage();
			}
		}
		
		return '';
	}
	
	/**
	 * Recursively traverses the package validation archives and returns the first exception.
	 * 
	 * @return	\Exception
	 */
	public function getException() {
		foreach ($this->getPackageValidationArchiveList() as $packageArchive) {
			if ($packageArchive->getException() !== null) {
				return $packageArchive->getException();
			}
		}
		
		return null;
	}
	
	/**
	 * Validates an instruction against the corresponding package installation plugin.
	 * 
	 * Please be aware that unknown PIPs will silently ignored and cause no error.
	 * 
	 * @param	PackageArchive		$archive
	 * @param	string			$pip
	 * @param	string			$instruction
	 * @return	boolean
	 */
	public function validatePackageInstallationPluginInstruction(PackageArchive $archive, $pip, $instruction) {
		if (isset($this->packageInstallationPlugins[$pip])) {
			return call_user_func([$this->packageInstallationPlugins[$pip], 'isValid'], $archive, $instruction);
		}
		
		return true;
	}
}
