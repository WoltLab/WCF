<?php
namespace wcf\system\package\validation;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\system\package\PackageArchive;

/**
 * Recursively validates the package archive and it's delivered requirements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.validation
 * @category	Community Framework
 */
class PackageValidationArchive implements \RecursiveIterator {
	/**
	 * package archive object
	 * @var	\wcf\system\package\PackageArchive
	 */
	protected $archive = null;
	
	/**
	 * list of direct requirements delivered by this package
	 * @var	array<\wcf\system\package\validation\PackageValidationArchive>
	 */
	protected $children = array();
	
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
	 * Validates this package and optionally it's delivered requirements. Unless you turn on
	 * $deepInspection, this will only check if the archive is theoretically usable to install
	 * or update. This means that neither exclusions nor dependencies will be checked. 
	 * 
	 * @param	boolean		$deepInspection
	 * @return	boolean
	 */
	public function validate($deepInspection, $requiredVersion = '') {
		try {
			// try to read archive
			$this->archive->openArchive();
			
			// check if package is installable or suitable for an update
			$this->validateInstructions($requiredVersion, $deepInspection);
		}
		catch (\Exception $e) {
			$this->exception = $e;
			
			return false;
		}
		
		if ($deepInspection) {
			try {
				PackageValidationManager::getInstance()->addVirtualPackage($this->archive->getPackageInfo('name'), $this->archive->getPackageInfo('version'));
				
				// check for exclusions
				// TODO: exclusions are not checked for testing purposes
				//	 REMOVE THIS BEFORE *ANY* PUBLIC RELEASE
				if (WCF_VERSION != '2.1.0 Alpha 1 (Typhoon)') {
					$this->validateExclusion();
				}
				
				// traverse open requirements
				foreach ($this->archive->getOpenRequirements() as $requirement) {
					$virtualPackageVersion = PackageValidationManager::getInstance()->getVirtualPackage($requirement['name']);
					if ($virtualPackageVersion === null || Package::compareVersion($virtualPackageVersion, $requirement['minversion'], '<')) {
						if (empty($requirement['file'])) {
							throw new PackageValidationException(PackageValidationException::MISSING_REQUIREMENT, array(
								'packageName' => $requirement['name'],
								'packageVersion' => $requirement['minversion']
							));
						}
						
						$archive = $this->archive->extractTar($requirement['file']);
						
						$index = count($this->children);
						$this->children[$index] = new PackageValidationArchive($archive, $this, $this->depth + 1);
						if (!$this->children[$index]->validate(true, $requirement['minversion'])) {
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
		
		return true;
		
	}
	
	/**
	 * Validates if the package has suitable install or update instructions. Setting $deepInspection
	 * to true will cause every single instruction to be validated against the corresponding PIP.
	 * 
	 * Please be aware that unknown PIPs will be silently ignored and will not cause any error!
	 * 
	 * @param	string		$requiredVersion
	 * @param	boolean		$deepInspection
	 */
	protected function validateInstructions($requiredVersion, $deepInspection) {
		$package = $this->getPackage();
		
		// delivered package does not provide the minimum required version
		if (Package::compareVersion($requiredVersion, $this->archive->getPackageInfo('version'), '>')) {
			throw new PackageValidationException(PackageValidationException::INSUFFICIENT_VERSION, array(
				'packageName' => $package->packageName,
				'packageVersion' => $package->packageVersion,
				'deliveredPackageVersion' => $this->archive->getPackageInfo('version')
			));
		}
		
		// package is not installed yet
		if ($package === null) {
			$instructions = $this->archive->getInstallInstructions();
			if (empty($instructions)) {
				throw new PackageValidationException(PackageValidationException::NO_INSTALL_PATH, array('packageName' => $this->archive->getPackageInfo('name')));
			}
			
			if ($deepInspection) {
				$this->validatePackageInstallationPlugins('install', $instructions);
			}
		}
		else {
			// package is already installed, check update path
			if (!$this->archive->isValidUpdate($package)) {
				throw new PackageValidationException(PackageValidationException::NO_UPDATE_PATH, array(
					'packageName' => $package->packageName,
					'packageVersion' => $package->packageVersion,
					'deliveredPackageVersion' => $this->archive->getPackageInfo('version')
				));
			}
			
			if ($deepInspection) {
				$this->validatePackageInstallationPlugins('update', $this->archive->getUpdateInstructions());
			}
		}
	}
	
	/**
	 * Validates install or update instructions against the corresponding PIP, unknown PIPs will be silently ignored.
	 * 
	 * @param	string		$type
	 * @param	array<array>	$instructions
	 */
	protected function validatePackageInstallationPlugins($type, array $instructions) {
		for ($i = 0, $length = count($instructions); $i < $length; $i++) {
			$instruction = $instructions[$i];
			if (!PackageValidationManager::getInstance()->validatePackageInstallationPluginInstruction($this->archive, $instruction['pip'], $instruction['value'])) {
				throw new PackageValidationException(PackageValidationException::MISSING_INSTRUCTION_FILE, array(
					'pip' => $instruction['pip'],
					'type' => $type,
					'value' => $instruction['value']
				));
			}
		}
	}
	
	/**
	 * Validates if an installed package excludes the current package and vice versa.
	 */
	protected function validateExclusion() {
		$excludingPackages = $this->archive->getConflictedExcludingPackages();
		if (!empty($excludingPackages)) {
			throw new PackageValidationException(PackageValidationException::EXCLUDING_PACKAGES, array('packages' => $excludingPackages));
		}
		
		$excludedPackages = $this->archive->getConflictedExcludedPackages();
		if (!empty($excludedPackages)) {
			throw new PackageValidationException(PackageValidationException::EXCLUDED_PACKAGES, array('packages' => $excludedPackages));
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
	 * @param	array<\wcf\system\package\validation\PackageValidationArchive>		$children
	 */
	public function setChildren(array $children) {
		$this->children = $children;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->children[$this->position]);
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->children[$this->position];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->position;
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function getChildren() {
		return $this->children[$this->position];
	}
	
	/**
	 * @see	\RecursiveIterator::hasChildren()
	 */
	public function hasChildren() {
		return count($this->children) > 0;
	}
}
