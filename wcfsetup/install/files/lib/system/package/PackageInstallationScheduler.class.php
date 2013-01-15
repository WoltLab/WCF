<?php
namespace wcf\system\package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Contains business logic related to preparation of package installations.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class PackageInstallationScheduler {
	/**
	 * list of packages to update or install
	 * @var	array
	 */
	protected $selectedPackages = array();
	
	/**
	 * list of package update server ids
	 * @var	array
	 */
	protected $packageUpdateServerIDs;
	
	/**
	 * enables downloading of updates
	 * @var	boolean
	 */
	protected $download;
	
	/**
	 * virtual package versions
	 * @var	array
	 */
	protected $virtualPackageVersions = array();
	
	/**
	 * stack of package installations / updates
	 * @var	array
	 */
	protected $packageInstallationStack = array();
	
	/**
	 * Creates a new instance of PackageInstallationScheduler
	 * 
	 * @param	array		$selectedPackages
	 * @param	array		$packageUpdateServerIDs
	 * @param	boolean		$download
	 */
	public function __construct(array $selectedPackages, array $packageUpdateServerIDs = array(), $download = true) {
		$this->selectedPackages = $selectedPackages;
		$this->packageUpdateServerIDs = $packageUpdateServerIDs;
		$this->download = $download;
	}
	
	/**
	 * Builds the stack of package installations / updates.
	 */
	public function buildPackageInstallationStack() {
		foreach ($this->selectedPackages as $package => $version) {
			if (is_numeric($package)) {
				$this->updatePackage($package, $version);
			}
			else {
				$this->tryToInstallPackage($package, $version, true);
			}
		}
	}
	
	/**
	 * Trys to install a new package. Checks the virtual package version list.
	 * 
	 * @param	string		$package		package identifier
	 * @param	string		$minversion		preferred package version
	 * @param	boolean		$installOldVersion	true, if you want to install the package in the given minversion and not in the newest version
	 */
	protected function tryToInstallPackage($package, $minversion = '', $installOldVersion = false) {
		// check virtual package version
		if (isset($this->virtualPackageVersions[$package])) {
			if (!empty($minversion) && Package::compareVersion($this->virtualPackageVersions[$package], $minversion, '<')) {
				$stackPosition = -1;
				// remove installation of older version
				foreach ($this->packageInstallationStack as $key => $value) {
					if ($value['package'] == $package) {
						$stackPosition = $key;
						break;
					}
				}
				
				// install newer version
				$this->installPackage($package, ($installOldVersion ? $minversion : ''), $stackPosition);
			}
		}
		else {
			// package is missing -> install
			$this->installPackage($package, ($installOldVersion ? $minversion : ''));
		}
	}
	
	/**
	 * Installs a new package.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @param	integer		$stackPosition
	 */
	protected function installPackage($package, $version = '', $stackPosition = -1) {
		// get package update versions
		$packageUpdateVersions = PackageUpdateDispatcher::getInstance()->getPackageUpdateVersions($package, $version);
		
		// resolve requirements
		$this->resolveRequirements($packageUpdateVersions[0]['packageUpdateVersionID']);
		
		// download package
		$download = '';
		if ($this->download) {
			$download = $this->downloadPackage($package, $packageUpdateVersions);
		}
		
		// add to stack
		$data = array(
			'packageName' => $packageUpdateVersions[0]['packageName'],
			'packageVersion' => $packageUpdateVersions[0]['packageVersion'],
			'package' => $package,
			'packageID' => 0,
			'archive' => $download,
			'action' => 'install'
		);
		if ($stackPosition == -1) $this->packageInstallationStack[] = $data;
		else $this->packageInstallationStack[$stackPosition] = $data;
		
		// update virtual versions
		$this->virtualPackageVersions[$package] = $packageUpdateVersions[0]['packageVersion'];
	}
	
	/**
	 * Resolves the package requirements of an package uppdate.
	 * Starts the installation or update to higher version of required packages.
	 * 
	 * @param	integer		$packageUpdateVersionID
	 */
	protected function resolveRequirements($packageUpdateVersionID) {
		// resolve requirements
		$requiredPackages = array();
		$requirementsCache = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_update_requirement
			WHERE	packageUpdateVersionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageUpdateVersionID));
		while ($row = $statement->fetchArray()) {
			$requiredPackages[] = $row['package'];
			$requirementsCache[] = $row;
		}
		
		if (!empty($requiredPackages)) {
			// find installed packages
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package IN (?)", array($requiredPackages));
			
			$installedPackages = array();
			$sql = "SELECT	packageID, package, packageVersion
				FROM	wcf".WCF_N."_package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!isset($installedPackages[$row['package']])) $installedPackages[$row['package']] = array();
				$installedPackages[$row['package']][$row['packageID']] = (isset($this->virtualPackageVersions[$row['packageID']]) ? $this->virtualPackageVersions[$row['packageID']] : $row['packageVersion']);
			}
			
			// check installed / missing packages
			foreach ($requirementsCache as $row) {
				if (isset($installedPackages[$row['package']])) {
					// package already installed -> check version
					// sort multiple instances by version number
					uasort($installedPackages[$row['package']], array('Package', 'compareVersion'));
					
					foreach ($installedPackages[$row['package']] as $packageID => $packageVersion) {
						if (empty($row['minversion']) || Package::compareVersion($row['minversion'], $packageVersion, '<=')) {
							continue 2;
						}
					}
					
					// package version too low -> update necessary
					$this->updatePackage($packageID, $row['minversion']);
				}
				else {
					$this->tryToInstallPackage($row['package'], $row['minversion']);
				}
			}
		}
	}
	
	/**
	 * Tries to download a package from available update servers.
	 * 
	 * @param	string		$package		package identifier
	 * @param	array		$packageUpdateVersions	package update versions
	 * @return	string		tmp filename of a downloaded package
	 */
	protected function downloadPackage($package, $packageUpdateVersions) {
		// get download from cache
		if ($filename = $this->getCachedDownload($package, $packageUpdateVersions[0]['package'])) {
			return $filename;
		}
		
		// download file
		$authorizationRequiredException = array();
		$systemExceptions = array();
		foreach ($packageUpdateVersions as $packageUpdateVersion) {
			try {
				// get auth data
				$authData = $this->getAuthData($packageUpdateVersion);
				
				// send request
				// TODO: Use HTTPRequest
				if (!empty($packageUpdateVersion['file'])) {
					$response = PackageUpdateDispatcher::getInstance()->sendRequest($packageUpdateVersion['file'], array(), $authData);
				}
				else {
					$response = PackageUpdateDispatcher::getInstance()->sendRequest($packageUpdateVersion['server'], array('packageName' => $packageUpdateVersion['package'], 'packageVersion' => $packageUpdateVersion['packageVersion']), $authData);
				}
				
				// check response
				// check http code
				if ($response['httpStatusCode'] == 401) {
					throw new PackageUpdateAuthorizationRequiredException($packageUpdateVersion['packageUpdateServerID'], (!empty($packageUpdateVersion['file']) ? $packageUpdateVersion['file'] : $packageUpdateVersion['server']), $response);
				}
				
				if ($response['httpStatusCode'] != 200) {
					throw new SystemException(WCF::getLanguage()->get('wcf.acp.packageUpdate.error.downloadFailed', array('$package' => $package)) . ' ('.$response['httpStatusLine'].')');
				}
				
				// write content to tmp file
				$filename = FileUtil::getTemporaryFilename('package_');
				$file = new File($filename);
				$file->write($response['content']);
				$file->close();
				unset($response['content']);
				
				// test package
				$archive = new PackageArchive($filename);
				$archive->openArchive();
				$archive->getTar()->close();
				
				// cache download in session
				PackageUpdateDispatcher::getInstance()->cacheDownload($package, $packageUpdateVersion['packageVersion'], $filename);
				
				return $filename;
			}
			catch (PackageUpdateAuthorizationRequiredException $e) {
				$authorizationRequiredException[] = $e;
			}
			catch (SystemException $e) {
				$systemExceptions[] = $e;
			}
		}
		
		if (!empty($authorizationRequiredException)) {
			throw array_shift($authorizationRequiredException);
		}
		
		if (!empty($systemExceptions)) {
			throw array_shift($systemExceptions);
		}
		
		return false;
	}
	
	/**
	 * Returns a list of excluded packages.
	 * 
	 * @return	array
	 */
	public function getExcludedPackages() {
		$excludedPackages = array();
		
		if (!empty($this->packageInstallationStack)) {
			$packageInstallations = array();
			$packageIdentifier = array();
			foreach ($this->packageInstallationStack as $packageInstallation) {
				$packageInstallation['newVersion'] = ($packageInstallation['action'] == 'update' ? $packageInstallation['toVersion'] : $packageInstallation['packageVersion']);
				$packageInstallations[] = $packageInstallation;
				$packageIdentifier[] = $packageInstallation['package'];
			}
			
			// check exclusions of the new packages
			// get package update ids
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package IN (?)", array($packageIdentifier));
			
			$sql = "SELECT	packageUpdateID, package
				FROM	wcf".WCF_N."_package_update
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				foreach ($packageInstallations as $key => $packageInstallation) {
					if ($packageInstallation['package'] == $row['package']) {
						$packageInstallations[$key]['packageUpdateID'] = $row['packageUpdateID'];
					}
				}
			}
			
			// get exclusions of the new packages
			// build conditions
			$conditions = '';
			$statementParameters = array();
			foreach ($packageInstallations as $packageInstallation) {
				if (!empty($conditions)) $conditions .= ' OR ';
				$conditions .= "(packageUpdateID = ? AND packageVersion = ?)";
				$statementParameters[] = $packageInstallation['packageUpdateID'];
				$statementParameters[] = $packageInstallation['newVersion'];
			}
			
			$sql = "SELECT		package.*, package_update_exclusion.*,
						package_update.packageUpdateID,
						package_update.package
				FROM		wcf".WCF_N."_package_update_exclusion package_update_exclusion
				LEFT JOIN	wcf".WCF_N."_package_update_version package_update_version
				ON		(package_update_version.packageUpdateVersionID = package_update_exclusion.packageUpdateVersionID)
				LEFT JOIN	wcf".WCF_N."_package_update package_update
				ON		(package_update.packageUpdateID = package_update_version.packageUpdateID)
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.package = package_update_exclusion.excludedPackage)
				WHERE		package_update_exclusion.packageUpdateVersionID IN (
							SELECT	packageUpdateVersionID
							FROM	wcf".WCF_N."_package_update_version
							WHERE	".$conditions."
						)
						AND package.package IS NOT NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($statementParameters);
			while ($row = $statement->fetchArray()) {
				foreach ($packageInstallations as $key => $packageInstallation) {
					if ($packageInstallation['package'] == $row['package']) {
						if (!isset($packageInstallations[$key]['excludedPackages'])) {
							$packageInstallations[$key]['excludedPackages'] = array();
						}
						$packageInstallations[$key]['excludedPackages'][$row['excludedPackage']] = array('package' => $row['excludedPackage'], 'version' => $row['excludedPackageVersion']);
						
						// check version
						if (!empty($row['excludedPackageVersion'])) {
							if (Package::compareVersion($row['packageVersion'], $row['excludedPackageVersion'], '<')) {
								continue;
							}
						}
						
						$excludedPackages[] = array(
							'package' => $row['package'],
							'packageName' => $packageInstallations[$key]['packageName'],
							'packageVersion' => $packageInstallations[$key]['newVersion'],
							'action' => $packageInstallations[$key]['action'],
							'conflict' => 'newPackageExcludesExistingPackage',
							'existingPackage' => $row['excludedPackage'],
							'existingPackageName' => $row['packageName'],
							'existingPackageVersion' => $row['packageVersion']
						);
					}
				}
			}
			
			// check excluded packages of the existing packages
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("excludePackage IN (?)", array($packageIdentifier));
			
			$sql = "SELECT		package.*, package_exclusion.*
				FROM		wcf".WCF_N."_package_exclusion package_exclusion
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = package_exclusion.packageID)
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				foreach ($packageInstallations as $key => $packageInstallation) {
					if ($packageInstallation['package'] == $row['excludedPackage']) {
						if (!empty($row['excludedPackageVersion'])) {
							// check version
							if (Package::compareVersion($packageInstallation['newVersion'], $row['excludedPackageVersion'], '<')) {
								continue;
							}
							
							// search exclusing package in stack
							foreach ($packageInstallations as $packageUpdate) {
								if ($packageUpdate['packageID'] == $row['packageID']) {
									// check new exclusions
									if (!isset($packageUpdate['excludedPackages']) || !isset($packageUpdate['excludedPackages'][$row['excludedPackage']]) || (!empty($packageUpdate['excludedPackages'][$row['excludedPackage']]['version']) && Package::compareVersion($packageInstallation['newVersion'], $packageUpdate['excludedPackages'][$row['excludedPackage']]['version'], '<'))) {
										continue 2;
									}
								}
							}
						}
						
						$excludedPackages[] = array(
							'package' => $row['excludedPackage'],
							'packageName' => $packageInstallation['packageName'],
							'packageVersion' => $packageInstallation['newVersion'],
							'action' => $packageInstallation['action'],
							'conflict' => 'existingPackageExcludesNewPackage',
							'existingPackage' => $row['package'],
							'existingPackageName' => $row['packageName'],
							'existingPackageVersion' => $row['packageVersion']
						);
					}
				}
			}
		}
		
		return $excludedPackages;
	}
	
	/**
	 * Returns the stack of package installations.
	 * 
	 * @return	array
	 */
	public function getPackageInstallationStack() {
		return $this->packageInstallationStack;
	}
	
	/**
	 * Gets the filename of in session stored donwloads.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @return	string		$filename
	 */
	protected function getCachedDownload($package, $version) {
		$cachedDownloads = WCF::getSession()->getVar('cachedPackageUpdateDownloads');
		if (isset($cachedDownloads[$package.'@'.$version]) && @file_exists($cachedDownloads[$package.'@'.$version])) {
			return $cachedDownloads[$package.'@'.$version];
		}
		
		return false;
	}
	
	/**
	 * Gets stored auth data of given update server.
	 * 
	 * @param	array		$updateServer
	 * @return	array		$authData
	 */
	protected function getAuthData(array $data) {
		$updateServer = new PackageUpdateServer(null, $data);
		return $updateServer->getAuthData();
	}
}
