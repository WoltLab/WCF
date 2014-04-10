<?php
namespace wcf\system\package\validation;
use wcf\system\package\PackageArchive;
use wcf\system\WCF;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;

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
	 * exception occured during validation
	 * @var	\Exception
	 */
	protected $exception = null;
	
	/**
	 * children pointer
	 * @var	integer
	 */
	private $position = 0;
	
	/**
	 * Creates a new package validation archive instance.
	 * 
	 * @param	string		$archive
	 */
	public function __construct($archive) {
		$this->archive = new PackageArchive($archive);
	}
	
	/**
	 * Validates this package and it's delivered requirements.
	 * 
	 * @param	boolean		$deepInspection
	 * @return	boolean
	 */
	public function validate($deepInspection = false, $requiredVersion = '') {
		try {
			// try to read archive
			$this->archive->openArchive();
			
			// check if package is installable or suitable for an update
			$this->validateInstructions($requiredVersion);
		}
		catch (\Exception $e) {
			$this->exception = $e;
			
			return false;
		}
		
		if ($deepInspection) {
			try {
				// check for exclusions
				$this->validateExclusion();
				
				// traverse open requirements
				foreach ($this->archive->getOpenRequirements() as $requirement) {
					$archive = $this->archive->extractTar($requirement->file);
						
					$index = count($this->children);
					$this->children[$index] = new PackageValidationArchive($archive);
					if (!$this->children[$index]->validate(true)) {
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
	
	protected function validateInstructions($requiredVersion) {
		$package = PackageCache::getInstance()->getPackageByIdentifier($this->archive->getPackageInfo('name'));
		
		// package is not installed yet
		if ($package === null) {
			if (empty($this->archive->getInstallInstructions())) {
				throw new PackageValidationException(PackageValidationException::NO_INSTALL_PATH, array('packageName' => $this->archive->getPackageInfo('name')));
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
		}
	}
	
	protected function validateExclusion() {
		$excludingPackages = $this->archive->getConflictedExcludingPackages();
		if (!empty($excludingPackages)) {
			throw new PackageValidationException(PackageValidationException::EXCLUDING_PACKAGES, array('packages' => $excludingPackages));
		}
		
		$excludedPackages = $this->archive->getConflictedExcludedPackages();
		if (!empty($excludingPackages)) {
			throw new PackageValidationException(PackageValidationException::EXCLUDED_PACKAGES, array('packages' => $excludedPackages));
		}
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
