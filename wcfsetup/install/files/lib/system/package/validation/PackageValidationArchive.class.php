<?php
namespace wcf\system\package\validation;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageArchive;
use wcf\system\WCF;

/**
 * Recursively validates the package archive and it's delivered requirements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Validation
 */
class PackageValidationArchive implements \RecursiveIterator {
	/**
	 * list of excluded packages grouped by package
	 * @var	string[]
	 */
	protected static $excludedPackages = [];
	
	/**
	 * package archive object
	 * @var	\wcf\system\package\PackageArchive
	 */
	protected $archive = null;
	
	/**
	 * list of direct requirements delivered by this package
	 * @var	PackageValidationArchive[]
	 */
	protected $children = [];
	
	/**
	 * nesting depth
	 * @var	integer
	 */
	protected $depth = 0;
	
	/**
	 * exception occured during validation
	 * @var	\Exception
	 */
	protected $exception = null;
	
	/**
	 * associated package object
	 * @var	\wcf\data\package\Package
	 */
	protected $package = null;
	
	/**
	 * parent package validation archive object
	 * @var	\wcf\system\package\validation\PackageValidationArchive
	 */
	protected $parent = null;
	
	/**
	 * children pointer
	 * @var	integer
	 */
	private $position = 0;
	
	/**
	 * Creates a new package validation archive instance.
	 * 
	 * @param	string								$archive
	 * @param	\wcf\system\package\validation\PackageValidationArchive		$parent
	 * @param	integer								$depth
	 */
	public function __construct($archive, PackageValidationArchive $parent = null, $depth = 0) {
		$this->archive = new PackageArchive($archive);
		$this->parent = $parent;
		$this->depth = $depth;
	}
	
	/**
	 * Validates this package and optionally it's delivered requirements. The set validation
	 * mode will toggle between different checks. 
	 * 
	 * @param	integer		$validationMode
	 * @param	string		$requiredVersion
	 * @return	boolean
	 */
	public function validate($validationMode, $requiredVersion = '') {
		if ($validationMode !== PackageValidationManager::VALIDATION_EXCLUSION) {
			try {
				// try to read archive
				$this->archive->openArchive();
				
				// check if package is installable or suitable for an update
				$this->validateInstructions($requiredVersion, $validationMode);
			}
			catch (PackageValidationException $e) {
				$this->exception = $e;
				
				return false;
			}
		}
		
		$package = $this->archive->getPackageInfo('name');
		
		if ($validationMode === PackageValidationManager::VALIDATION_RECURSIVE) {
			try {
				PackageValidationManager::getInstance()->addVirtualPackage($package, $this->archive->getPackageInfo('version'));
				
				// cache excluded packages
				self::$excludedPackages[$package] = [];
				$excludedPackages = $this->archive->getExcludedPackages();
				for ($i = 0, $count = count($excludedPackages); $i < $count; $i++) {
					if (!isset(self::$excludedPackages[$package][$excludedPackages[$i]['name']])) {
						self::$excludedPackages[$package][$excludedPackages[$i]['name']] = [];
					}
					
					self::$excludedPackages[$package][$excludedPackages[$i]['name']][] = $excludedPackages[$i]['version'];
				}
				
				// traverse open requirements
				foreach ($this->archive->getOpenRequirements() as $requirement) {
					$virtualPackageVersion = PackageValidationManager::getInstance()->getVirtualPackage($requirement['name']);
					if ($virtualPackageVersion === null || Package::compareVersion($virtualPackageVersion, $requirement['minversion'], '<')) {
						if (empty($requirement['file'])) {
							// check if package is known
							$sql = "SELECT	*
								FROM	wcf".WCF_N."_package
								WHERE	package = ?";
							$statement = WCF::getDB()->prepareStatement($sql);
							$statement->execute([$requirement['name']]);
							$package = $statement->fetchObject(Package::class);
							
							throw new PackageValidationException(PackageValidationException::MISSING_REQUIREMENT, [
								'package' => $package,
								'packageName' => $requirement['name'],
								'packageVersion' => $requirement['minversion']
							]);
						}
						
						$archive = $this->archive->extractTar($requirement['file']);
						
						$index = count($this->children);
						$this->children[$index] = new PackageValidationArchive($archive, $this, $this->depth + 1);
						if (!$this->children[$index]->validate(PackageValidationManager::VALIDATION_RECURSIVE, $requirement['minversion'])) {
							return false;
						}
						
						PackageValidationManager::getInstance()->addVirtualPackage(
							$this->children[$index]->getArchive()->getPackageInfo('name'),
							$this->children[$index]->getArchive()->getPackageInfo('version')
						);
					}
				}
			}
			catch (PackageValidationException $e) {
				$this->exception = $e;
				
				return false;
			}
		}
		else if ($validationMode === PackageValidationManager::VALIDATION_EXCLUSION) {
			try {
				$this->validateExclusion($package);
				
				for ($i = 0, $count = count($this->children); $i < $count; $i++) {
					if (!$this->children[$i]->validate(PackageValidationManager::VALIDATION_EXCLUSION)) {
						return false;
					}
				}
			}
			catch (PackageValidationException $e) {
				$this->exception = $e;
				
				return false;
			}
		}
		
		return true;
		
	}
	
	/**
	 * Validates if the package has suitable install or update instructions
	 * 
	 * @param	string		$requiredVersion
	 * @param	integer		$validationMode
	 * @throws	PackageValidationException
	 */
	protected function validateInstructions($requiredVersion, $validationMode) {
		$package = $this->getPackage();
		
		// delivered package does not provide the minimum required version
		if (Package::compareVersion($requiredVersion, $this->archive->getPackageInfo('version'), '>')) {
			throw new PackageValidationException(PackageValidationException::INSUFFICIENT_VERSION, [
				'packageName' => $package->packageName,
				'packageVersion' => $package->packageVersion,
				'deliveredPackageVersion' => $this->archive->getPackageInfo('version')
			]);
		}
		
		// package is not installed yet
		if ($package === null) {
			$instructions = $this->archive->getInstallInstructions();
			if (empty($instructions)) {
				throw new PackageValidationException(PackageValidationException::NO_INSTALL_PATH, ['packageName' => $this->archive->getPackageInfo('name')]);
			}
			
			if ($validationMode == PackageValidationManager::VALIDATION_RECURSIVE) {
				$this->validatePackageInstallationPlugins('install', $instructions);
			}
		}
		else {
			// package is already installed, check update path
			if (!$this->archive->isValidUpdate($package)) {
				throw new PackageValidationException(PackageValidationException::NO_UPDATE_PATH, [
					'packageName' => $package->packageName,
					'packageVersion' => $package->packageVersion,
					'deliveredPackageVersion' => $this->archive->getPackageInfo('version')
				]);
			}
			
			if ($validationMode === PackageValidationManager::VALIDATION_RECURSIVE) {
				$this->validatePackageInstallationPlugins('update', $this->archive->getUpdateInstructions());
			}
		}
	}
	
	/**
	 * Validates install or update instructions against the corresponding PIP, unknown PIPs will be silently ignored.
	 * 
	 * @param	string		$type
	 * @param	mixed[][]	$instructions
	 * @throws	PackageValidationException
	 */
	protected function validatePackageInstallationPlugins($type, array $instructions) {
		for ($i = 0, $length = count($instructions); $i < $length; $i++) {
			$instruction = $instructions[$i];
			if (!PackageValidationManager::getInstance()->validatePackageInstallationPluginInstruction($this->archive, $instruction['pip'], $instruction['value'])) {
				throw new PackageValidationException(PackageValidationException::MISSING_INSTRUCTION_FILE, [
					'pip' => $instruction['pip'],
					'type' => $type,
					'value' => $instruction['value']
				]);
			}
		}
	}
	
	/**
	 * Validates if an installed package excludes the current package and vice versa.
	 * 
	 * @param	string		$package
	 * @throws	PackageValidationException
	 */
	protected function validateExclusion($package) {
		$packageVersion = $this->archive->getPackageInfo('version');
		
		// excluding packages: installed -> current
		$sql = "SELECT		package.*, package_exclusion.*
			FROM		wcf".WCF_N."_package_exclusion package_exclusion
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_exclusion.packageID)
			WHERE		excludedPackage = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->getArchive()->getPackageInfo('name')]);
		$excludingPackages = [];
		while ($row = $statement->fetchArray()) {
			$excludingPackage = $row['package'];
			
			// use exclusions of queued package
			if (isset(self::$excludedPackages[$excludingPackage])) {
				if (isset(self::$excludedPackages[$excludingPackage][$package])) {
					for ($i = 0, $count = count(self::$excludedPackages[$excludingPackage][$package]); $i < $count; $i++) {
						if (Package::compareVersion($packageVersion, self::$excludedPackages[$excludingPackage][$package][$i], '<')) {
							continue;
						}
						
						$excludingPackages[] = new Package(null, $row);
					}
					
					continue;
				}
			}
			else {
				if (Package::compareVersion($packageVersion, $row['excludedPackageVersion'], '<')) {
					continue;
				}
				
				$excludingPackages[] = new Package(null, $row);
			}
		}
		
		if (!empty($excludingPackages)) {
			throw new PackageValidationException(PackageValidationException::EXCLUDING_PACKAGES, ['packages' => $excludingPackages]);
		}
		
		// excluded packages: current -> installed
		if (!empty(self::$excludedPackages[$package])) {
			// get installed packages
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package IN (?)", [array_keys(self::$excludedPackages[$package])]);
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$packages = [];
			while ($row = $statement->fetchArray()) {
				$packages[$row['package']] = new Package(null, $row);
			}
			
			$excludedPackages = [];
			foreach ($packages as $excludedPackage => $packageObj) {
				$version = PackageValidationManager::getInstance()->getVirtualPackage($excludedPackage);
				if ($version === null) {
					$version = $packageObj->packageVersion;
				}
				
				for ($i = 0, $count = count(self::$excludedPackages[$package][$excludedPackage]); $i < $count; $i++) {
					if (Package::compareVersion($version, self::$excludedPackages[$package][$excludedPackage][$i], '<')) {
						continue;
					}
					
					$excludedPackages[] = $packageObj;
				}
			}
			
			if (!empty($excludedPackages)) {
				throw new PackageValidationException(PackageValidationException::EXCLUDED_PACKAGES, ['packages' => $excludedPackages]);
			}
		}
	}
	
	/**
	 * Returns the occured exception.
	 * 
	 * @return	\Exception
	 */
	public function getException() {
		return $this->exception;
	}
	
	/**
	 * Returns the exception message.
	 * 
	 * @return	string
	 */
	public function getExceptionMessage() {
		if ($this->exception === null) {
			return '';
		}
		
		if ($this->exception instanceof PackageValidationException) {
			return $this->exception->getErrorMessage();
		}
		
		return $this->exception->getMessage();
	}
	
	/**
	 * Returns the package archive object.
	 * 
	 * @return	\wcf\system\package\PackageArchive
	 */
	public function getArchive() {
		return $this->archive;
	}
	
	/**
	 * Returns the package object based on the package archive's package identifier or null
	 * if the package isn't already installed.
	 * 
	 * @return	\wcf\data\package\Package
	 */
	public function getPackage() {
		if ($this->package === null) {
			$this->package = PackageCache::getInstance()->getPackageByIdentifier($this->archive->getPackageInfo('name'));
		}
		
		return $this->package;
	}
	
	/**
	 * Returns nesting depth.
	 * 
	 * @return	integer
	 */
	public function getDepth() {
		return $this->depth;
	}
	
	/**
	 * Sets the children of this package validation archive.
	 * 
	 * @param	PackageValidationArchive[]	$children
	 */
	public function setChildren(array $children) {
		$this->children = $children;
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return isset($this->children[$this->position]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		return $this->children[$this->position];
	}
	
	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->position;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getChildren() {
		return $this->children[$this->position];
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasChildren() {
		return count($this->children) > 0;
	}
}
