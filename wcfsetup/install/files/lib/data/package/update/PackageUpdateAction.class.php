<?php
namespace wcf\data\package\update;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\version\PackageUpdateVersion;
use wcf\data\package\Package;
use wcf\data\search\Search;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageInstallationScheduler;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\package\PackageUpdateUnauthorizedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Executes package update-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update
 * 
 * @method	PackageUpdate		create()
 * @method	PackageUpdateEditor[]	getObjects()
 * @method	PackageUpdateEditor	getSingleObject()
 */
class PackageUpdateAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PackageUpdateEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['getResultList', 'prepareInstallation', 'prepareUpdate', 'search', 'searchForUpdates'];
	
	/**
	 * search object
	 * @var	Search
	 */
	protected $search = null;
	
	/**
	 * Validates parameters to search for installable packages.
	 */
	public function validateSearch() {
		WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
		
		$this->readString('searchString');
	}
	
	/**
	 * Returns a result list of a search for installable packages.
	 * 
	 * @return	array
	 */
	public function search() {
		$availableUpdateServers = PackageUpdateServer::getActiveUpdateServers();
		
		// there are no available package update servers
		if (empty($availableUpdateServers)) {
			WCF::getTPL()->assign([
				'officialPackages' => [],
				'thirdPartySources' => [],
				'trustedSources' => [],
			]);
			
			return ['count' => 0, 'pageCount' => 0, 'searchID' => 0, 'template' => WCF::getTPL()->fetch('packageSearchResultList')];
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("package_update.packageUpdateServerID IN (?)", [array_keys($availableUpdateServers)]);
		$searchString = '%' . $this->parameters['searchString'] . '%';
		$conditions->add("(package_update.package LIKE ? OR package_update.packageDescription LIKE ? OR package_update.packageName LIKE ?)", [$searchString, $searchString, $searchString]);
		$conditions->add("package.packageID IS NULL");
		
		// find matching packages
		$sql = "SELECT		package_update.packageUpdateID
			FROM		wcf" . WCF_N . "_package_update package_update
			LEFT JOIN	wcf" . WCF_N . "_package package
			ON		(package.package = package_update.package)
			" . $conditions . "
			ORDER BY	package_update.packageName ASC";
		$statement = WCF::getDB()->prepareStatement($sql, 1000);
		$statement->execute($conditions->getParameters());
		$packageUpdateIDs = [];
		while ($row = $statement->fetchArray()) {
			$packageUpdateIDs[] = $row['packageUpdateID'];
		}
		
		// no matches found
		if (empty($packageUpdateIDs)) {
			WCF::getTPL()->assign([
				'officialPackages' => [],
				'thirdPartySources' => [],
				'trustedSources' => [],
			]);
			
			return ['count' => 0, 'pageCount' => 0, 'searchID' => 0, 'template' => WCF::getTPL()->fetch('packageSearchResultList')];
		}
		
		// get installed packages
		$sql = "SELECT	package, packageVersion
			FROM	wcf".WCF_N."_package";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$installedPackages = $statement->fetchMap('package', 'packageVersion');
		
		// get excluded packages (of installed packages)
		$excludedPackagesOfInstalledPackages = [];
		$sql = "SELECT	excludedPackage, excludedPackageVersion
			FROM	wcf".WCF_N."_package_exclusion";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!isset($excludedPackagesOfInstalledPackages[$row['excludedPackage']])) {
				$excludedPackagesOfInstalledPackages[$row['excludedPackage']] = $row['excludedPackageVersion'];
			}
			else if (Package::compareVersion($excludedPackagesOfInstalledPackages[$row['excludedPackage']], $row['excludedPackageVersion'], '>')) {
				$excludedPackagesOfInstalledPackages[$row['excludedPackage']] = $row['excludedPackageVersion'];
			}
		}
		
		$packageUpdates = [];
		foreach ($packageUpdateIDs as $packageUpdateID) {
			$result = $this->canInstall($packageUpdateID, null, $installedPackages, $excludedPackagesOfInstalledPackages);
			if (isset($result[$packageUpdateID])) $packageUpdates[$packageUpdateID] = $result[$packageUpdateID];
		}
		
		// no matches found
		if (empty($packageUpdates)) {
			WCF::getTPL()->assign([
				'officialPackages' => [],
				'thirdPartySources' => [],
				'trustedSources' => [],
			]);
			
			return ['count' => 0, 'pageCount' => 0, 'searchID' => 0, 'template' => WCF::getTPL()->fetch('packageSearchResultList')];
		}
		
		// remove duplicates by picking either the lowest available version of a package
		// or the version exposed by trusted package servers
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateID IN (?)", [array_keys($packageUpdates)]);
		$sql = "SELECT  packageUpdateID, packageUpdateServerID, package
			FROM    wcf".WCF_N."_package_update
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$possiblePackages = [];
		while ($row = $statement->fetchArray()) {
			$possiblePackages[$row['package']][$row['packageUpdateID']] = $row['packageUpdateServerID'];
		}
		
		$trustedServerIDs = [];
		foreach (PackageUpdateServer::getActiveUpdateServers() as $packageUpdateServer) {
			if ($packageUpdateServer->isTrustedServer() || $packageUpdateServer->isWoltLabStoreServer()) {
				$trustedServerIDs[] = $packageUpdateServer->packageUpdateServerID;
			}
		}
		
		// remove duplicates when there are both versions from trusted and untrusted servers
		foreach ($possiblePackages as $identifier => $packageSources) {
			$hasTrustedSource = false;
			foreach ($packageSources as $packageUpdateID => $packageUpdateServerID) {
				if (in_array($packageUpdateServerID, $trustedServerIDs)) {
					$hasTrustedSource = true;
					break;
				}
			}
			
			if ($hasTrustedSource) {
				$possiblePackages[$identifier] = array_filter($packageSources, function($packageUpdateServerID) use ($trustedServerIDs) {
					return in_array($packageUpdateServerID, $trustedServerIDs);
				});
			}
		}
		
		// Sort by the highest version and return all other sources for the same package.
		$validPackageUpdateIDs = [];
		foreach ($possiblePackages as $identifier => $packageSources) {
			if (count($packageSources) > 1) {
				$packageUpdateVersionIDs = [];
				foreach (array_keys($packageSources) as $packageUpdateID) {
					$packageUpdateVersionIDs[] = $packageUpdates[$packageUpdateID]['accessible'];
				}
				
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("packageUpdateVersionID IN (?)", [$packageUpdateVersionIDs]);
				
				$sql = "SELECT  packageUpdateVersionID, packageUpdateID, packageVersion
					FROM    wcf".WCF_N."_package_update_version
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$packageVersions = [];
				while ($row = $statement->fetchArray()) {
					$packageVersions[$row['packageUpdateVersionID']] = [
						'packageUpdateID' => $row['packageUpdateID'],
						'packageVersion' => $row['packageVersion'],
					];
				}
				
				// Sort packages with the highest version ending up on top.
				uasort($packageVersions, function($a, $b) {
					return Package::compareVersion($b['packageVersion'], $a['packageVersion']);
				});
				
				reset($packageVersions);
				$validPackageUpdateIDs[] = current($packageVersions)['packageUpdateID'];
			}
			else {
				reset($packageSources);
				$validPackageUpdateIDs[] = key($packageSources);
			}
		}
		
		// filter by package update version ids
		foreach ($packageUpdates as $packageUpdateID => $packageData) {
			if (!in_array($packageUpdateID, $validPackageUpdateIDs)) {
				unset($packageUpdates[$packageUpdateID]);
			}
		}
		
		return $this->getResultList($availableUpdateServers, $packageUpdates);
	}
	
	/**
	 * Validates dependencies and exclusions of a package,
	 * optionally limited by a minimum version number.
	 * 
	 * @param       integer         $packageUpdateID
	 * @param       string|null     $minVersion
	 * @param       string[]        $installedPackages
	 * @param       string[]        $excludedPackagesOfInstalledPackages
	 * @return      string[][]
	 */
	protected function canInstall($packageUpdateID, $minVersion, array &$installedPackages, array &$excludedPackagesOfInstalledPackages) {
		// get excluded packages
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateVersionID IN (SELECT packageUpdateVersionID FROM wcf".WCF_N."_package_update_version WHERE packageUpdateID = ?)", [$packageUpdateID]);
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_update_exclusion
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$excludedPackages = [];
		while ($row = $statement->fetchArray()) {
			$package = $row['excludedPackage'];
			$packageVersion = $row['excludedPackageVersion'];
			$packageUpdateVersionID = $row['packageUpdateVersionID'];
			
			if (!isset($excludedPackages[$packageUpdateVersionID][$package])) {
				$excludedPackages[$packageUpdateVersionID][$package] = $packageVersion;
			}
			else if (Package::compareVersion($excludedPackages[$packageUpdateVersionID][$package], $packageVersion) == 1) {
				$excludedPackages[$packageUpdateVersionID][$package] = $packageVersion;
			}
		}
		
		// filter by version
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("puv.packageUpdateID IN (?)", [$packageUpdateID]);
		$sql = "SELECT		pu.package, puv.packageUpdateVersionID, puv.packageUpdateID, puv.packageVersion, puv.isAccessible
			FROM		wcf".WCF_N."_package_update_version puv
			LEFT JOIN	wcf".WCF_N."_package_update pu
			ON		(pu.packageUpdateID = puv.packageUpdateID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageVersions = [];
		while ($row = $statement->fetchArray()) {
			$package = $row['package'];
			$packageVersion = $row['packageVersion'];
			$packageUpdateVersionID = $row['packageUpdateVersionID'];
			
			if ($minVersion !== null && Package::compareVersion($packageVersion, $minVersion) == -1) {
				continue;
			}
			
			// check excluded packages
			if (isset($excludedPackages[$packageUpdateVersionID])) {
				$isExcluded = false;
				foreach ($excludedPackages[$packageUpdateVersionID] as $excludedPackage => $excludedPackageVersion) {
					if (isset($installedPackages[$excludedPackage]) && Package::compareVersion($excludedPackageVersion, $installedPackages[$excludedPackage]) <= 0) {
						// excluded, ignore
						$isExcluded = true;
						break;
					}
				}
				
				if ($isExcluded) {
					continue;
				}
			}
			// check excluded packages of installed packages
			if (isset($excludedPackagesOfInstalledPackages[$row['package']])) {
				if (Package::compareVersion($packageVersion, $excludedPackagesOfInstalledPackages[$row['package']], '>=')) {
					continue;
				}
			}
			
			if (!isset($packageVersions[$package])) {
				$packageVersions[$package] = [];
			}
			
			$packageUpdateID = $row['packageUpdateID'];
			if (!isset($packageVersions[$package][$packageUpdateID])) {
				$packageVersions[$package][$packageUpdateID] = [
					'accessible' => [],
					'existing' => [],
				];
			}
			
			if ($row['isAccessible']) {
				$packageVersions[$package][$packageUpdateID]['accessible'][$row['packageUpdateVersionID']] = $packageVersion;
			}
			$packageVersions[$package][$packageUpdateID]['existing'][$row['packageUpdateVersionID']] = $packageVersion;
		}
		
		// all found versions are excluded
		if (empty($packageVersions)) return [];
		
		// determine highest versions
		$packageUpdates = [];
		foreach ($packageVersions as $package => $versionData) {
			$accessible = $existing = $versions = [];
			
			foreach ($versionData as $packageUpdateID => $versionTypes) {
				// ignore inaccessible packages
				if (empty($versionTypes['accessible'])) {
					continue;
				}
				
				uasort($versionTypes['accessible'], [Package::class, 'compareVersion']);
				uasort($versionTypes['existing'], [Package::class, 'compareVersion']);
				
				$accessibleVersion = array_slice($versionTypes['accessible'], -1, 1, true);
				$existingVersion = array_slice($versionTypes['existing'], -1, 1, true);
				
				$ak = key($accessibleVersion);
				$av = current($accessibleVersion);
				$ek = key($existingVersion);
				$ev = current($existingVersion);
				
				$accessible[$av] = $ak;
				$existing[$ev] = $ek;
				$versions[$ak] = $packageUpdateID;
				$versions[$ek] = $packageUpdateID;
			}
			
			// ignore packages without accessible versions
			if (empty($accessible)) {
				continue;
			}
			
			uksort($accessible, [Package::class, 'compareVersion']);
			uksort($existing, [Package::class, 'compareVersion']);
			
			$accessible = array_pop($accessible);
			$existing = array_pop($existing);
			$packageUpdates[$versions[$accessible]] = [
				'accessible' => $accessible,
				'existing' => $existing,
			];
		}
		
		// validate dependencies
		foreach ($packageUpdates as $packageUpdateData) {
			$sql = "SELECT  package, minversion
				FROM    wcf".WCF_N."_package_update_requirement
				WHERE   packageUpdateVersionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$packageUpdateData['accessible']]);
			$requirements = [];
			while ($row = $statement->fetchArray()) {
				$package = $row['package'];
				$minVersion = $row['minversion'];
				
				if (!isset($installedPackages[$package]) || Package::compareVersion($installedPackages[$package], $minVersion) == -1) {
					$requirements[$package] = $minVersion;
				}
			}
			
			if (empty($requirements)) continue;
			
			$openRequirements = array_keys($requirements);
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("package IN (?)", [array_keys($requirements)]);
			$sql = "SELECT  packageUpdateID, package
				FROM    wcf".WCF_N."_package_update
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!in_array($row['package'], $openRequirements)) {
					// The dependency has already been satisfied by another update server.
					continue;
				}
				
				$result = $this->canInstall($row['packageUpdateID'], $requirements[$row['package']], $installedPackages, $excludedPackagesOfInstalledPackages);
				if (!empty($result)) {
					$index = array_search($row['package'], $openRequirements);
					unset($openRequirements[$index]);
				}
			}
			
			if (!empty($openRequirements)) {
				return [];
			}
		}
		
		return $packageUpdates;
	}
	
	/**
	 * Returns a result list for a previous search.
	 * 
	 * @param       PackageUpdateServer[]   $updateServers
	 * @param       array                   $updateData
	 * @return	array
	 */
	protected function getResultList(array $updateServers, array $updateData) {
		// get package updates
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateID IN (?)", [array_keys($updateData)]);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_update
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageUpdates = $packageVersionIDs = [];
		while ($packageUpdate = $statement->fetchObject(PackageUpdate::class)) {
			$packageUpdates[$packageUpdate->packageUpdateID] = new ViewablePackageUpdate($packageUpdate);
			
			// collect package version ids
			$versionIDs = $updateData[$packageUpdate->packageUpdateID];
			$packageVersionIDs[] = $versionIDs['accessible'];
			$packageVersionIDs[] = $versionIDs['existing'];
		}
		
		// read update versions
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateVersionID IN (?)", [$packageVersionIDs]);
		
		$sql = "SELECT	packageUpdateVersionID, packageVersion, packageDate, license, licenseURL
			FROM	wcf".WCF_N."_package_update_version
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		/** @var PackageUpdateVersion[] $updateVersions */
		$updateVersions = $statement->fetchObjects(PackageUpdateVersion::class, 'packageUpdateVersionID');
		
		// assign versions
		/**
		 * @var ViewablePackageUpdate[] $officialPackages
		 * @var ViewablePackageUpdate[] $thirdPartySources
		 * @var ViewablePackageUpdate[] $trustedSources
		 */
		$officialPackages = $thirdPartySources = $trustedSources = [];
		/**
		 * @var int $packageUpdateID
		 * @var ViewablePackageUpdate $packageUpdate
		 */
		foreach ($packageUpdates as $packageUpdateID => $packageUpdate) {
			$versionIDs = $updateData[$packageUpdate->packageUpdateID];
			$packageUpdate->setAccessibleVersion($updateVersions[$versionIDs['accessible']]);
			$packageUpdate->setLatestVersion($updateVersions[$versionIDs['existing']]);
			$packageUpdate->setUpdateServer($updateServers[$packageUpdate->packageUpdateServerID]);
			
			if ($packageUpdate->getUpdateServer()->isWoltLabUpdateServer()) {
				$officialPackages[] = $packageUpdate;
			}
			else if ($packageUpdate->getUpdateServer()->isTrustedServer() || $packageUpdate->getUpdateServer()->isWoltLabStoreServer()) {
				$trustedSources[] = $packageUpdate;
			}
			else {
				$thirdPartySources[] = $packageUpdate;
			}
		}
		
		uasort($officialPackages, function(ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
			return strnatcasecmp($a->getName(), $b->getName());
		});
		uasort($thirdPartySources, function(ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
			return strnatcasecmp($a->getName(), $b->getName());
		});
		uasort($trustedSources, function(ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
			return strnatcasecmp($a->getName(), $b->getName());
		});
		
		WCF::getTPL()->assign([
			'officialPackages' => $officialPackages,
			'thirdPartySources' => $thirdPartySources,
			'trustedSources' => $trustedSources,
		]);
		
		return [
			'count' => count($officialPackages) + count($thirdPartySources) + count($trustedSources),
			'template' => WCF::getTPL()->fetch('packageSearchResultList'),
		];
	}
	
	/**
	 * Validates permissions to search for updates.
	 */
	public function validateSearchForUpdates() {
		WCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);
		
		$this->readBoolean('ignoreCache', true);
		
		if (ENABLE_BENCHMARK) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.acp.package.searchForUpdates.benchmark'));
		}
	}
	
	/**
	 * Searches for updates.
	 * 
	 * @return	array
	 */
	public function searchForUpdates() {
		PackageUpdateDispatcher::getInstance()->refreshPackageDatabase([], $this->parameters['ignoreCache']);
		
		$updates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
		$url = '';
		if (!empty($updates)) {
			$url = LinkHandler::getInstance()->getLink('PackageUpdate');
		}
		
		return [
			'url' => $url,
		];
	}
	
	/**
	 * Validates parameters to perform a system update.
	 */
	public function validatePrepareUpdate() {
		WCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);
		
		if (!isset($this->parameters['packages']) || !is_array($this->parameters['packages'])) {
			throw new UserInputException('packages');
		}
		
		// validate packages for their existence
		$availableUpdates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
		foreach ($this->parameters['packages'] as $packageName => $versionNumber) {
			$isValid = false;
			
			foreach ($availableUpdates as $package) {
				if ($package['package'] == $packageName) {
					// validate version
					if (isset($package['versions'][$versionNumber])) {
						$isValid = true;
						break;
					}
				}
			}
			
			if (!$isValid) {
				throw new UserInputException('packages');
			}
		}
		
		if (isset($this->parameters['authData'])) {
			if (!is_array($this->parameters['authData'])) {
				throw new UserInputException('authData');
			}
			
			$this->readInteger('packageUpdateServerID', false, 'authData');
			$this->readString('password', false, 'authData');
			$this->readString('username', false, 'authData');
			$this->readBoolean('saveCredentials', true, 'authData');
		}
	}
	
	/**
	 * Prepares a system update.
	 * 
	 * @return	array
	 */
	public function prepareUpdate() {
		return $this->createQueue('update');
	}
	
	/**
	 * Validates parameters to prepare a package installation.
	 */
	public function validatePrepareInstallation() {
		WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
		
		if (!isset($this->parameters['packages']) || !is_array($this->parameters['packages']) || count($this->parameters['packages']) != 1) {
			throw new UserInputException('packages');
		}
		
		if (isset($this->parameters['authData'])) {
			if (!is_array($this->parameters['authData'])) {
				throw new UserInputException('authData');
			}
			
			$this->readInteger('packageUpdateServerID', false, 'authData');
			$this->readString('password', false, 'authData');
			$this->readString('username', false, 'authData');
			$this->readBoolean('saveCredentials', true, 'authData');
		}
	}
	
	/**
	 * Prepares a package installation.
	 * 
	 * @return	array
	 */
	public function prepareInstallation() {
		return $this->createQueue('install');
	}
	
	public function validateRefreshDatabase() {
		WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
		
		$this->readBoolean('ignoreCache', true);
		
		if (ENABLE_BENCHMARK) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.acp.package.searchForUpdates.benchmark'));
		}
	}
	
	public function refreshDatabase() {
		PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();
	}
	
	/**
	 * Creates a new package installation queue.
	 * 
	 * @param	string		$queueType
	 * @return	array
	 * @throws	SystemException
	 */
	protected function createQueue($queueType) {
		if (isset($this->parameters['authData'])) {
			PackageUpdateServer::storeAuthData($this->parameters['authData']['packageUpdateServerID'], $this->parameters['authData']['username'], $this->parameters['authData']['password'], $this->parameters['authData']['saveCredentials']);
		}
		
		$scheduler = new PackageInstallationScheduler($this->parameters['packages']);
		
		try {
			$scheduler->buildPackageInstallationStack($queueType == 'install');
		}
		catch (PackageUpdateUnauthorizedException $e) {
			return [
				'template' => $e->getRenderedTemplate(),
			];
		}
		
		// validate exclusions
		if ($queueType == 'update') {
			$excludedPackages = $scheduler->getExcludedPackages();
			
			if (!empty($excludedPackages)) {
				return [
					'excludedPackages' => true,
					'template' => WCF::getTPL()->fetch('packageUpdateExcludedPackages', 'wcf', ['excludedPackages' => $excludedPackages]),
				];
			}
		}
		
		$stack = $scheduler->getPackageInstallationStack();
		
		// detect major upgrades of the Core itself
		if ($queueType === 'update') {
			for ($i = 0, $length = count($stack); $i < $length; $i++) {
				$update = $stack[$i];
				if ($update['package'] === 'com.woltlab.wcf') {
					preg_match('~^(?P<version>\d+\.\d+)\.~', $update['fromversion'], $matchFromVersion);
					preg_match('~^(?P<version>\d+\.\d+)\.~', $update['toVersion'], $matchToVersion);
					if ($matchFromVersion['version'] != $matchToVersion['version']) {
						// version mismatch, this is a major upgrade
						if ($i > 0) {
							// the Core upgrade must be the first package in the stack, but there appears to be
							// at least one package in front of the queue, therefore there are outstanding
							// updates for the previous version line
							throw new SystemException(WCF::getLanguage()->getDynamicVariable('wcf.acp.package.update.error.outstandingUpdates'));
						}
					}
					
					break;
				}
			}
		}
		
		$queueID = null;
		if (!empty($stack)) {
			$parentQueueID = 0;
			$processNo = PackageInstallationQueue::getNewProcessNo();
			foreach ($stack as $package) {
				$queue = PackageInstallationQueueEditor::create([
					'parentQueueID' => $parentQueueID,
					'processNo' => $processNo,
					'userID' => WCF::getUser()->userID,
					'package' => $package['package'],
					'packageName' => $package['packageName'],
					'packageID' => $package['packageID'] ?: null,
					'archive' => $package['archive'],
					'action' => $package['action'],
				]);
				$parentQueueID = $queue->queueID;
				
				if ($queueID === null) {
					$queueID = $queue->queueID;
				}
			}
		}
		
		return [
			'queueID' => $queueID,
		];
	}
}
