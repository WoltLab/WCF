<?php
namespace wcf\system\package;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\io\Tar;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\XML;

/**
 * Represents the archive of a package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageArchive {
	/**
	 * path to package archive
	 * @var	string
	 */
	protected $archive;
	
	/**
	 * package object of an existing package
	 * @var	Package
	 */
	protected $package;
	
	/**
	 * tar archive object
	 * @var	Tar
	 */
	protected $tar;
	
	/**
	 * general package information
	 * @var	array
	 */
	protected $packageInfo = [];
	
	/**
	 * author information
	 * @var	array
	 */
	protected $authorInfo = [];
	
	/**
	 * list of requirements
	 * @var	array
	 */
	protected $requirements = [];
	
	/**
	 * list of optional packages
	 * @var	array
	 */
	protected $optionals = [];
	
	/**
	 * list of excluded packages
	 * @var	array
	 */
	protected $excludedPackages = [];
	
	/**
	 * list of compatible API versions
	 * @var integer[]
	 * @deprecated 5.2
	 */
	protected $compatibility = [];
	
	/**
	 * list of instructions
	 * @var	mixed[][]
	 */
	protected $instructions = [
		'install' => [],
		'update' => []
	];
	
	/**
	 * default name of the package.xml file
	 * @var	string
	 */
	const INFO_FILE = 'package.xml';
	
	/**
	 * marker for the void instruction
	 * @var	string
	 */
	const VOID_MARKER = "===void===";
	
	/**
	 * Creates a new PackageArchive object.
	 * 
	 * @param	string		$archive
	 * @param	Package		$package
	 */
	public function __construct($archive, Package $package = null) {
		$this->archive = $archive;	// be careful: this is a string within this class, 
						// but an object in the packageStartInstallForm.class!
		$this->package = $package;
	}
	
	/**
	 * Sets associated package object.
	 * 
	 * @param	Package		$package
	 */
	public function setPackage(Package $package) {
		$this->package = $package;
	}
	
	/**
	 * Returns the name of the package archive.
	 * 
	 * @return	string
	 */
	public function getArchive() {
		return $this->archive;
	}
	
	/**
	 * Returns the object of the package archive.
	 * 
	 * @return	Tar
	 */
	public function getTar() {
		return $this->tar;
	}
	
	/**
	 * Opens the package archive and reads package information.
	 */
	public function openArchive() {
		// check whether archive exists and is a TAR archive
		if (!file_exists($this->archive)) {
			throw new PackageValidationException(PackageValidationException::FILE_NOT_FOUND, ['archive' => $this->archive]);
		}
		
		// open archive and read package information
		$this->tar = new Tar($this->archive);
		$this->readPackageInfo();
	}
	
	/**
	 * Extracts information about this package (parses package.xml).
	 */
	protected function readPackageInfo() {
		// search package.xml in package archive
		// throw error message if not found
		if ($this->tar->getIndexByFilename(self::INFO_FILE) === false) {
			throw new PackageValidationException(PackageValidationException::MISSING_PACKAGE_XML, ['archive' => $this->archive]);
		}
		
		// extract package.xml, parse XML
		// and compile an array with XML::getElementTree()
		$xml = new XML();
		try {
			$xml->loadXML(self::INFO_FILE, $this->tar->extractToString(self::INFO_FILE));
		}
		catch (\Exception $e) { // bugfix to avoid file caching problems
			$xml->loadXML(self::INFO_FILE, $this->tar->extractToString(self::INFO_FILE));
		}
		
		// parse xml
		$xpath = $xml->xpath();
		/** @var \DOMElement $package */
		$package = $xpath->query('/ns:package')->item(0);
		
		// package name
		$packageName = $package->getAttribute('name');
		if (!Package::isValidPackageName($packageName)) {
			// package name is not a valid package identifier
			throw new PackageValidationException(PackageValidationException::INVALID_PACKAGE_NAME, ['packageName' => $packageName]);
		}
		
		$this->packageInfo['name'] = $packageName;
		
		// get package information
		$packageInformation = $xpath->query('./ns:packageinformation', $package)->item(0);
		$elements = $xpath->query('child::*', $packageInformation);
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			switch ($element->tagName) {
				case 'packagename':
				case 'packagedescription':
				case 'readme':
				case 'license':
					if (!isset($this->packageInfo[$element->tagName])) $this->packageInfo[$element->tagName] = [];
					
					$languageCode = 'default';
					if ($element->hasAttribute('language')) {
						$languageCode = $element->getAttribute('language');
					}
					
					// fix case-sensitive names
					$name = $element->tagName;
					if ($name == 'packagename') $name = 'packageName';
					else if ($name == 'packagedescription') $name = 'packageDescription';
					
					$this->packageInfo[$name][$languageCode] = $element->nodeValue;
				break;
				
				case 'isapplication':
					$this->packageInfo['isApplication'] = intval($element->nodeValue);
				break;
				
				case 'applicationdirectory':
					if (preg_match('~^[a-z0-9\-\_]+$~', $element->nodeValue)) {
						$this->packageInfo['applicationDirectory'] = $element->nodeValue;
					}
				break;
				
				case 'packageurl':
					$this->packageInfo['packageURL'] = $element->nodeValue;
				break;
				
				case 'version':
					if (!Package::isValidVersion($element->nodeValue)) {
						throw new PackageValidationException(PackageValidationException::INVALID_PACKAGE_VERSION, ['packageVersion' => $element->nodeValue]);
					}
					
					$this->packageInfo['version'] = $element->nodeValue;
				break;
				
				case 'date':
					DateUtil::validateDate($element->nodeValue);
					
					$this->packageInfo['date'] = @strtotime($element->nodeValue);
				break;
			}
		}
		
		// get author information
		$authorInformation = $xpath->query('./ns:authorinformation', $package)->item(0);
		$elements = $xpath->query('child::*', $authorInformation);
		foreach ($elements as $element) {
			$tagName = ($element->tagName == 'authorurl') ? 'authorURL' : $element->tagName;
			$this->authorInfo[$tagName] = $element->nodeValue;
		}
		
		// get required packages
		$elements = $xpath->query('child::ns:requiredpackages/ns:requiredpackage', $package);
		foreach ($elements as $element) {
			if (!Package::isValidPackageName($element->nodeValue)) {
				throw new PackageValidationException(PackageValidationException::INVALID_PACKAGE_NAME, ['packageName' => $element->nodeValue]);
			}
			
			// read attributes
			$data = ['name' => $element->nodeValue];
			$attributes = $xpath->query('attribute::*', $element);
			foreach ($attributes as $attribute) {
				$data[$attribute->name] = $attribute->value;
			}
			
			$this->requirements[$element->nodeValue] = $data;
		}
		
		// get optional packages
		$elements = $xpath->query('child::ns:optionalpackages/ns:optionalpackage', $package);
		foreach ($elements as $element) {
			if (!Package::isValidPackageName($element->nodeValue)) {
				throw new PackageValidationException(PackageValidationException::INVALID_PACKAGE_NAME, ['packageName' => $element->nodeValue]);
			}
			
			// read attributes
			$data = ['name' => $element->nodeValue];
			$attributes = $xpath->query('attribute::*', $element);
			foreach ($attributes as $attribute) {
				$data[$attribute->name] = $attribute->value;
			}
			
			$this->optionals[] = $data;
		}
		
		// get excluded packages
		$elements = $xpath->query('child::ns:excludedpackages/ns:excludedpackage', $package);
		foreach ($elements as $element) {
			if (!Package::isValidPackageName($element->nodeValue)) {
				throw new PackageValidationException(PackageValidationException::INVALID_PACKAGE_NAME, ['packageName' => $element->nodeValue]);
			}
			
			// read attributes
			$data = ['name' => $element->nodeValue];
			$attributes = $xpath->query('attribute::*', $element);
			foreach ($attributes as $attribute) {
				$data[$attribute->name] = $attribute->value;
			}
			
			$this->excludedPackages[] = $data;
		}
		
		// get api compatibility
		$elements = $xpath->query('child::ns:compatibility/ns:api', $package);
		foreach ($elements as $element) {
			if (!$element->hasAttribute('version')) continue;
			
			$version = $element->getAttribute('version');
			if (!preg_match('~^(?:201[7-9]|20[2-9][0-9])$~', $version)) {
				throw new PackageValidationException(PackageValidationException::INVALID_API_VERSION, ['version' => $version]);
			}
			
			$this->compatibility[] = $version;
		}
		
		// API compatibility implies an exclude of `com.woltlab.wcf` in version `6.0.0 Alpha 1`, unless a lower version is explicitly excluded.
		if (!empty($this->compatibility)) {
			$excludeCore60 = '6.0.0 Alpha 1';
			
			$coreExclude = '';
			foreach ($this->excludedPackages as $excludedPackage) {
				if ($excludedPackage['name'] === 'com.woltlab.wcf') {
					$coreExclude = $excludedPackage['version'];
					break;
				}
			}
			
			if (!$coreExclude || Package::compareVersion($coreExclude, $excludeCore60, '>')) {
				if ($coreExclude) {
					$this->excludedPackages = array_filter($this->excludedPackages, function($exclude) {
						return $exclude['name'] !== 'com.woltlab.wcf';
					});
				}
				
				$this->excludedPackages[] = [
					'name' => 'com.woltlab.wcf',
					'version' => $excludeCore60,
				];
			}
		}
		
		// get instructions
		$elements = $xpath->query('./ns:instructions', $package);
		foreach ($elements as $element) {
			$instructionData = [];
			$instructions = $xpath->query('./ns:instruction', $element);
			/** @var \DOMElement $instruction */
			foreach ($instructions as $instruction) {
				$data = [];
				$attributes = $xpath->query('attribute::*', $instruction);
				foreach ($attributes as $attribute) {
					$data[$attribute->name] = $attribute->value;
				}
				
				$instructionData[] = [
					'attributes' => $data,
					'pip' => $instruction->getAttribute('type'),
					'value' => $instruction->nodeValue
				];
			}
			
			$fromVersion = $element->getAttribute('fromversion');
			$type = $element->getAttribute('type');
			
			$void = $xpath->query('./ns:void', $element);
			if ($void->length > 1) {
				throw new PackageValidationException(PackageValidationException::VOID_NOT_ALONE);
			}
			else if ($void->length == 1) {
				if (!empty($instructionData)) {
					throw new PackageValidationException(PackageValidationException::VOID_NOT_ALONE);
				}
				if ($type == 'install') {
					throw new PackageValidationException(PackageValidationException::VOID_ON_INSTALL);
				}
				
				$instructionData[] = [
					'pip' => self::VOID_MARKER,
					'value' => '',
				];
			}
			
			if ($type == 'install') {
				$this->instructions['install'] = $instructionData;
			}
			else {
				$this->instructions['update'][$fromVersion] = $instructionData;
			}
		}
		
		// add com.woltlab.wcf to package requirements
		if (!isset($this->requirements['com.woltlab.wcf']) && $this->packageInfo['name'] != 'com.woltlab.wcf') {
			$this->requirements['com.woltlab.wcf'] = ['name' => 'com.woltlab.wcf'];
		}
		
		// during installations, `Package::$packageVersion` can be `null` which causes issues
		// in `PackageArchive::filterUpdateInstructions()`; as update instructions are not needed
		// for installations, not filtering update instructions is okay
		if ($this->package !== null && $this->package->packageVersion !== null) {
			$this->filterUpdateInstructions();
		}
		
		// set default values
		if (!isset($this->packageInfo['isApplication'])) $this->packageInfo['isApplication'] = 0;
		if (!isset($this->packageInfo['packageURL'])) $this->packageInfo['packageURL'] = '';
	}
	
	/**
	 * Filters update instructions.
	 */
	protected function filterUpdateInstructions() {
		$validFromVersion = null;
		foreach ($this->instructions['update'] as $fromVersion => $update) {
			if (Package::checkFromversion($this->package->packageVersion, $fromVersion)) {
				$validFromVersion = $fromVersion;
				break;
			}
		}
		
		if ($validFromVersion === null) {
			$this->instructions['update'] = [];
		}
		else {
			$this->instructions['update'] = $this->instructions['update'][$validFromVersion];
		}
	}
	
	/**
	 * Downloads the package archive.
	 * 
	 * @return	string		path to the dowloaded file
	 */
	public function downloadArchive() {
		$prefix = 'package';
		
		// file transfer via hypertext transfer protocol.
		$this->archive = FileUtil::downloadFileFromHttp($this->archive, $prefix);
		
		// unzip tar
		$this->archive = self::unzipPackageArchive($this->archive);
		
		return $this->archive;
	}
	
	/**
	 * Closes and deletes the tar archive of this package. 
	 */
	public function deleteArchive() {
		if ($this->tar instanceof Tar) {
			$this->tar->close();
		}
		
		@unlink($this->archive);
	}
	
	/**
	 * Returns true if the package archive supports a new installation.
	 * 
	 * @return	boolean
	 */
	public function isValidInstall() {
		return !empty($this->instructions['install']);
	}
	
	/**
	 * Checks if the new package is compatible with
	 * the package that is about to be updated.
	 * 
	 * @param	Package		$package
	 * @return	boolean		isValidUpdate
	 */
	public function isValidUpdate(Package $package = null) {
		if ($this->package === null && $package !== null) {
			$this->setPackage($package);
			
			// re-evaluate update data
			$this->filterUpdateInstructions();
		}
		
		// Check name of the installed package against the name of the update. Both must be identical.
		if ($this->packageInfo['name'] != $this->package->package) {
			return false;
		}
		
		// Check if the version number of the installed package is lower than the version number to which
		// it's about to be updated.
		if (Package::compareVersion($this->packageInfo['version'], $this->package->packageVersion) != 1) {
			return false;
		}
		
		// Check if the package provides an instructions block for the update from the installed package version
		if (empty($this->instructions['update'])) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Checks if the current package is already installed, as it is not
	 * possible to install non-applications multiple times within the
	 * same environment.
	 * 
	 * @return	boolean
	 */
	public function isAlreadyInstalled() {
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package
			WHERE	package = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->packageInfo['name']]);
		
		return $statement->fetchSingleColumn() > 0;
	}
	
	/**
	 * Returns true if the package is an application and has an unique abbreviation.
	 * 
	 * @return	boolean
	 */
	public function hasUniqueAbbreviation() {
		if (!$this->packageInfo['isApplication']) {
			return true;
		}
		
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package
			WHERE	isApplication = ?
				AND package LIKE ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			1,
			'%.'.Package::getAbbreviation($this->packageInfo['name'])
		]);
		
		return $statement->fetchSingleColumn() > 0;
	}
	
	/**
	 * Returns information about the author of this package archive.
	 * 
	 * @param	string		$name		name of the requested information
	 * @return	string
	 */
	public function getAuthorInfo($name) {
		if (isset($this->authorInfo[$name])) return $this->authorInfo[$name];
		return null;
	}
	
	/**
	 * Returns information about this package.
	 * 
	 * @param	string		$name		name of the requested information
	 * @return	mixed
	 */
	public function getPackageInfo($name) {
		if (isset($this->packageInfo[$name])) return $this->packageInfo[$name];
		return null;
	}
	
	/**
	 * Returns a localized information about this package.
	 * 
	 * @param	string		$name
	 * @return	string
	 */
	public function getLocalizedPackageInfo($name) {
		if (isset($this->packageInfo[$name][WCF::getLanguage()->getFixedLanguageCode()])) {
			return $this->packageInfo[$name][WCF::getLanguage()->getFixedLanguageCode()];
		}
		else if (isset($this->packageInfo[$name]['default'])) {
			return $this->packageInfo[$name]['default'];
		}
		
		if (!empty($this->packageInfo[$name])) {
			return reset($this->packageInfo[$name]);
		}
		
		return '';
	}
	
	/**
	 * Returns a list of all requirements of this package.
	 * 
	 * @return	array
	 */
	public function getRequirements() {
		return $this->requirements;
	}
	
	/**
	 * Returns a list of all delivered optional packages of this package.
	 * 
	 * @return	array
	 */
	public function getOptionals() {
		return $this->optionals;
	}
	
	/**
	 * Returns a list of excluded packages.
	 * 
	 * @return	array
	 */
	public function getExcludedPackages() {
		return $this->excludedPackages;
	}
	
	/**
	 * Returns the list of compatible API versions.
	 * 
	 * @return      integer[]
	 * @deprecated 5.2
	 */
	public function getCompatibleVersions() {
		return $this->compatibility;
	}
	
	/**
	 * Returns the package installation instructions.
	 * 
	 * @return	array
	 */
	public function getInstallInstructions() {
		return $this->instructions['install'];
	}
	
	/**
	 * Returns the package update instructions.
	 * 
	 * @return	array
	 */
	public function getUpdateInstructions() {
		return $this->instructions['update'];
	}
	
	/**
	 * Checks which package requirements do already exist in right version.
	 * Returns a list with all existing requirements.
	 * 
	 * @return	array
	 */
	public function getAllExistingRequirements() {
		$existingRequirements = [];
		$existingPackages = [];
		if ($this->package !== null) {
			$sql = "SELECT		package.*
				FROM		wcf".WCF_N."_package_requirement requirement
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = requirement.requirement)
				WHERE		requirement.packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->package->packageID]);
			while ($row = $statement->fetchArray()) {
				$existingRequirements[$row['package']] = $row;
			}
		}
		
		// build sql
		$packageNames = [];
		$requirements = $this->getRequirements();
		foreach ($requirements as $requirement) {
			if (isset($existingRequirements[$requirement['name']])) {
				$existingPackages[$requirement['name']] = [];
				$existingPackages[$requirement['name']][$existingRequirements[$requirement['name']]['packageID']] = $existingRequirements[$requirement['name']];
			}
			else {
				$packageNames[] = $requirement['name'];
			}
		}
		
		// check whether the required packages do already exist
		if (!empty($packageNames)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package.package IN (?)", [$packageNames]);
			
			$sql = "SELECT	package.*
				FROM	wcf".WCF_N."_package package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				// check required package version
				if (isset($requirements[$row['package']]['minversion']) && Package::compareVersion($row['packageVersion'], $requirements[$row['package']]['minversion']) == -1) {
					continue;
				}
				
				if (!isset($existingPackages[$row['package']])) {
					$existingPackages[$row['package']] = [];
				}
				
				$existingPackages[$row['package']][$row['packageID']] = $row;
			}
		}
		
		return $existingPackages;
	}
	
	/**
	 * Checks which package requirements do already exist in database.
	 * Returns a list with the existing requirements.
	 * 
	 * @return	array
	 */
	public function getExistingRequirements() {
		// build sql
		$packageNames = [];
		foreach ($this->requirements as $requirement) {
			$packageNames[] = $requirement['name'];
		}
		
		// check whether the required packages do already exist
		$existingPackages = [];
		if (!empty($packageNames)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package IN (?)", [$packageNames]);
			
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!isset($existingPackages[$row['package']])) {
					$existingPackages[$row['package']] = [];
				}
				
				$existingPackages[$row['package']][$row['packageVersion']] = $row;
			}
			
			// sort multiple packages by version number
			foreach ($existingPackages as $packageName => $instances) {
				uksort($instances, [Package::class, 'compareVersion']);
				
				// get package with highest version number (get last package)
				$existingPackages[$packageName] = array_pop($instances);
			}
		}
		
		return $existingPackages;
	}
	
	/**
	 * Returns a list of all open requirements of this package.
	 * 
	 * @return	array
	 */
	public function getOpenRequirements() {
		// get all existing requirements
		$existingPackages = $this->getExistingRequirements();
		
		// check for open requirements
		$openRequirements = [];
		foreach ($this->requirements as $requirement) {
			if (isset($existingPackages[$requirement['name']])) {
				// package does already exist
				// maybe an update is necessary
				if (isset($requirement['minversion'])) {
					if (Package::compareVersion($existingPackages[$requirement['name']]['packageVersion'], $requirement['minversion']) >= 0) {
						// package does already exist in needed version
						// skip installation of requirement
						continue;
					}
					else {
						$requirement['existingVersion'] = $existingPackages[$requirement['name']]['packageVersion'];
					}
				}
				else {
					continue;
				}
				
				$requirement['packageID'] = $existingPackages[$requirement['name']]['packageID'];
				$requirement['action'] = 'update';
			}
			else {
				// package does not exist
				// new installation is necessary
				$requirement['packageID'] = 0;
				$requirement['action'] = 'install';
			}
			
			$openRequirements[$requirement['name']] = $requirement;
		}
		
		return $openRequirements;
	}
	
	/**
	 * Extracts the requested file in the package archive to the temp folder
	 * and returns the path to the extracted file.
	 * 
	 * @param	string		$filename
	 * @param	string		$tempPrefix
	 * @return	string
	 * @throws	PackageValidationException
	 */
	public function extractTar($filename, $tempPrefix = 'package_') {
		// search the requested tar archive in our package archive.
		// throw error message if not found.
		if (($fileIndex = $this->tar->getIndexByFilename($filename)) === false) {
			throw new PackageValidationException(PackageValidationException::FILE_NOT_FOUND, [
				'archive' => $this->archive,
				'targetArchive' => $filename
			]);
		}
		
		// requested tar archive was found
		$fileInfo = $this->tar->getFileInfo($fileIndex);
		$filename = FileUtil::getTemporaryFilename($tempPrefix, preg_replace('!^.*?(\.(?:tar\.gz|tgz|tar))$!i', '\\1', $fileInfo['filename']));
		$this->tar->extract($fileIndex, $filename);
		
		return $filename;
	}
	
	/**
	 * Unzips compressed package archives and returns the temporary file name.
	 * 
	 * @param	string		$archive	filename
	 * @return	string
	 */
	public static function unzipPackageArchive($archive) {
		if (!FileUtil::isURL($archive)) {
			$tar = new Tar($archive);
			$tar->close();
			if ($tar->isZipped()) {
				$tmpName = FileUtil::getTemporaryFilename('package_');
				if (FileUtil::uncompressFile($archive, $tmpName)) {
					return $tmpName;
				}
			}
		}
		
		return $archive;
	}
	
	/**
	 * Returns a list of packages which exclude this package.
	 * 
	 * @return	Package[]
	 */
	public function getConflictedExcludingPackages() {
		$conflictedPackages = [];
		$sql = "SELECT		package.*, package_exclusion.*
			FROM		wcf".WCF_N."_package_exclusion package_exclusion
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_exclusion.packageID)
			WHERE		excludedPackage = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->packageInfo['name']]);
		while ($row = $statement->fetchArray()) {
			if (!empty($row['excludedPackageVersion'])) {
				if (Package::compareVersion($this->packageInfo['version'], $row['excludedPackageVersion'], '<')) {
					continue;
				}
			}
			
			$conflictedPackages[$row['packageID']] = new Package(null, $row);
		}
		
		return $conflictedPackages;
	}
	
	/**
	 * Returns a list of packages which are excluded by this package.
	 * 
	 * @return	Package[]
	 */
	public function getConflictedExcludedPackages() {
		$conflictedPackages = [];
		if (!empty($this->excludedPackages)) {
			$excludedPackages = [];
			foreach ($this->excludedPackages as $excludedPackageData) {
				$excludedPackages[$excludedPackageData['name']] = $excludedPackageData['version'];
			}
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package IN (?)", [array_keys($excludedPackages)]);
			
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!empty($excludedPackages[$row['package']])) {
					if (Package::compareVersion($row['packageVersion'], $excludedPackages[$row['package']], '<')) {
						continue;
					}
					$row['excludedPackageVersion'] = $excludedPackages[$row['package']];
				}
				
				$conflictedPackages[$row['packageID']] = new Package(null, $row);
			}
		}
		
		return $conflictedPackages;
	}
	
	/**
	 * Returns a list of instructions for installation or update.
	 * 
	 * @param	string		$type
	 * @return	array
	 */
	public function getInstructions($type) {
		if (isset($this->instructions[$type])) {
			return $this->instructions[$type];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of php requirements for current package.
	 * 
	 * @return	mixed[][]
	 * @deprecated  3.0
	 */
	public function getPhpRequirements() {
		return [];
	}
}
