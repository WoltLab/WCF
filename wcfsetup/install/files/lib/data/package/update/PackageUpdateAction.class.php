<?php
namespace wcf\data\package\update;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\Package;
use wcf\data\search\Search;
use wcf\data\search\SearchEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 */
class PackageUpdateAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\update\PackageUpdateEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('getResultList', 'prepareInstallation', 'prepareUpdate', 'search', 'searchForUpdates');
	
	/**
	 * search object
	 * @var	\wcf\data\search\Search
	 */
	protected $search = null;
	
	/**
	 * Validates parameters to search for installable packages.
	 */
	public function validateSearch() {
		WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
		
		$this->readString('package', true);
		$this->readString('packageDescription', true);
		$this->readString('packageName', true);
		$this->readBoolean('searchDescription', true);
		
		if (empty($this->parameters['package']) && empty($this->parameters['packageDescription']) && empty($this->parameters['packageName'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns a result list of a search for installable packages.
	 * 
	 * @return	array
	 */
	public function search() {
		PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();
		$availableUpdateServers = PackageUpdateServer::getActiveUpdateServers();
		
		// there are no available package update servers
		if (empty($availableUpdateServers)) {
			WCF::getTPL()->assign(array(
				'packageUpdates' => array()
			));
			
			return array(
				'count' => 0,
				'pageCount' => 0,
				'searchID' => 0,
				'template' => WCF::getTPL()->fetch('packageSearchResultList')
			);
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("package_update.packageUpdateServerID IN (?)", array(array_keys($availableUpdateServers)));
		if (!empty($this->parameters['package'])) {
			$conditions->add("package_update.package LIKE ?", array('%'.$this->parameters['package'].'%'));
		}
		if (!empty($this->parameters['packageDescription'])) {
			$conditions->add("package_update.packageDescription LIKE ?", array('%'.$this->parameters['packageDescription'].'%'));
		}
		if (!empty($this->parameters['packageName'])) {
			$conditions->add("package_update.packageName LIKE ?", array('%'.$this->parameters['packageName'].'%'));
		}
		$conditions->add("package.packageID IS NULL");
		
		// find matching packages
		$sql = "SELECT		package_update.packageUpdateID
			FROM		wcf".WCF_N."_package_update package_update
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.package = package_update.package)
			".$conditions."
			ORDER BY	package_update.packageName ASC";
		$statement = WCF::getDB()->prepareStatement($sql, 1000);
		$statement->execute($conditions->getParameters());
		$packageUpdateIDs = array();
		while ($row = $statement->fetchArray()) {
			$packageUpdateIDs[] = $row['packageUpdateID'];
		}
		
		// no matches found
		if (empty($packageUpdateIDs)) {
			WCF::getTPL()->assign(array(
				'packageUpdates' => array()
			));
			
			return array(
				'count' => 0,
				'pageCount' => 0,
				'searchID' => 0,
				'template' => WCF::getTPL()->fetch('packageSearchResultList')
			);
		}
		
		// get excluded packages
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_update_exclusion";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$excludedPackages = array();
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
		
		// get installed packages
		$sql = "SELECT	package, packageVersion
			FROM	wcf".WCF_N."_package";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$installedPackages = array();
		while ($row = $statement->fetchArray()) {
			$installedPackages[$row['package']] = $row['packageVersion'];
		}
		
		// filter by version
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("puv.packageUpdateID IN (?)", array($packageUpdateIDs));
		$sql = "SELECT		pu.package, puv.packageUpdateVersionID, puv.packageUpdateID, puv.packageVersion, puv.isAccessible
			FROM		wcf".WCF_N."_package_update_version puv
			LEFT JOIN	wcf".WCF_N."_package_update pu
			ON		(pu.packageUpdateID = puv.packageUpdateID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageVersions = array();
		while ($row = $statement->fetchArray()) {
			$package = $row['package'];
			$packageVersion = $row['packageVersion'];
			$packageUpdateVersionID = $row['packageUpdateVersionID'];
			
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
			
			if (!isset($packageVersions[$package])) {
				$packageVersions[$package] = array();
			}
			
			$packageUpdateID = $row['packageUpdateID'];
			if (!isset($packageVersions[$package][$packageUpdateID])) {
				$packageVersions[$package][$packageUpdateID] = array(
					'accessible' => array(),
					'existing' => array()
				);
			}
			
			if ($row['isAccessible']) {
				$packageVersions[$package][$packageUpdateID]['accessible'][$row['packageUpdateVersionID']] = $packageVersion;
			}
			$packageVersions[$package][$packageUpdateID]['existing'][$row['packageUpdateVersionID']] = $packageVersion;
		}
		
		// all found versions are excluded
		if (empty($packageVersions)) {
			WCF::getTPL()->assign(array(
				'packageUpdates' => array()
			));
			
			return array(
				'count' => 0,
				'pageCount' => 0,
				'searchID' => 0,
				'template' => WCF::getTPL()->fetch('packageSearchResultList')
			);
		}
		
		// determine highest versions
		$packageUpdates = array();
		foreach ($packageVersions as $package => $versionData) {
			$accessible = $existing = $versions = array();
			
			foreach ($versionData as $packageUpdateID => $versionTypes) {
				// ignore unaccessible packages
				if (empty($versionTypes['accessible'])) {
					continue;
				}
				
				uasort($versionTypes['accessible'], array('wcf\data\package\Package', 'compareVersion'));
				uasort($versionTypes['existing'], array('wcf\data\package\Package', 'compareVersion'));
				
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
			
			uksort($accessible, array('wcf\data\package\Package', 'compareVersion'));
			uksort($existing, array('wcf\data\package\Package', 'compareVersion'));
			
			$accessible = array_pop($accessible);
			$existing = array_pop($existing);
			$packageUpdates[$versions[$accessible]] = array(
				'accessible' => $accessible,
				'existing' => $existing
			);
		}
		
		// no found packages is accessible
		if (empty($packageUpdates)) {
			WCF::getTPL()->assign(array(
				'packageUpdates' => array()
			));
			
			return array(
				'count' => 0,
				'pageCount' => 0,
				'searchID' => 0,
				'template' => WCF::getTPL()->fetch('packageSearchResultList')
			);
		}
		
		$search = SearchEditor::create(array(
			'userID' => WCF::getUser()->userID,
			'searchData' => serialize($packageUpdates),
			'searchTime' => TIME_NOW,
			'searchType' => 'acpPackageSearch'
		));
		
		// forward call to build the actual result list
		$updateAction = new PackageUpdateAction(array(), 'getResultList', array(
			'pageNo' => 1,
			'search' => $search
		));
		
		$returnValues = $updateAction->executeAction();
		return $returnValues['returnValues'];
	}
	
	/**
	 * Validates parameters to return a result list for a previous search.
	 */
	public function validateGetResultList() {
		WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
		
		$this->readInteger('pageNo');
		$this->readInteger('searchID');
		
		$this->search = new Search($this->parameters['searchID']);
		if (!$this->search->searchID || $this->search->userID != WCF::getUser()->userID) {
			throw new UserInputException('searchID');
		}
	}
	
	/**
	 * Returns a result list for a previous search.
	 * 
	 * @return	array
	 */
	public function getResultList() {
		if ($this->search === null && isset($this->parameters['search']) && $this->parameters['search'] instanceof Search) {
			$this->search = $this->parameters['search'];
		}
		$updateData = unserialize($this->search->searchData);
		
		// get package updates
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateID IN (?)", array(array_keys($updateData)));
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_update
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
		$statement->execute($conditions->getParameters());
		$packageUpdates = $packageVersionIDs = array();
		while ($packageUpdate = $statement->fetchObject('wcf\data\package\update\PackageUpdate')) {
			$packageUpdates[$packageUpdate->packageUpdateID] = new ViewablePackageUpdate($packageUpdate);
			
			// collect package version ids
			$versionIDs = $updateData[$packageUpdate->packageUpdateID];
			$packageVersionIDs[] = $versionIDs['accessible'];
			$packageVersionIDs[] = $versionIDs['existing'];
		}
		
		// read update versions
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateVersionID IN (?)", array($packageVersionIDs));
		
		$sql = "SELECT	packageUpdateVersionID, packageVersion, packageDate, license, licenseURL
			FROM	wcf".WCF_N."_package_update_version
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$updateVersions = array();
		while ($updateVersion = $statement->fetchObject('wcf\data\package\update\version\PackageUpdateVersion')) {
			$updateVersions[$updateVersion->packageUpdateVersionID] = $updateVersion;
		}
		
		// assign versions
		foreach ($packageUpdates as $packageUpdateID => $packageUpdate) {
			$versionIDs = $updateData[$packageUpdate->packageUpdateID];
			$packageUpdate->setAccessibleVersion($updateVersions[$versionIDs['accessible']]);
			$packageUpdate->setLatestVersion($updateVersions[$versionIDs['existing']]);
		}
		
		WCF::getTPL()->assign(array(
			'packageUpdates' => $packageUpdates
		));
		
		$count = count($updateData);
		return array(
			'count' => $count,
			'pageCount' => ceil($count / 20),
			'searchID' => $this->search->searchID,
			'template' => WCF::getTPL()->fetch('packageSearchResultList')
		);
	}
	
	/**
	 * Validates permissions to search for updates.
	 */
	public function validateSearchForUpdates() {
		WCF::getSession()->checkPermissions(array('admin.system.package.canUpdatePackage'));
		
		$this->readBoolean('ignoreCache', true);
	}
	
	/**
	 * Searches for updates.
	 * 
	 * @return	array
	 */
	public function searchForUpdates() {
		PackageUpdateDispatcher::getInstance()->refreshPackageDatabase(array(), $this->parameters['ignoreCache']);
		
		$updates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
		$url = '';
		if (!empty($updates)) {
			$url = LinkHandler::getInstance()->getLink('PackageUpdate');
		}
		
		return array(
			'url' => $url
		);
	}
	
	/**
	 * Validates parameters to perform a system update.
	 */
	public function validatePrepareUpdate() {
		WCF::getSession()->checkPermissions(array('admin.system.package.canUpdatePackage'));
		
		if (!isset($this->parameters['packages']) || !is_array($this->parameters['packages'])) {
			throw new UserInputException('packages');
		}
		
		// validate packages for their existance
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
		WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
		
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
	
	/**
	 * Creates a new package installation queue.
	 * 
	 * @param	string		$queueType
	 * @return	array
	 */
	protected function createQueue($queueType) {
		if (isset($this->parameters['authData'])) {
			PackageUpdateServer::storeAuthData($this->parameters['authData']['packageUpdateServerID'], $this->parameters['authData']['username'], $this->parameters['authData']['password'], $this->parameters['authData']['saveCredentials']);
		}
		
		$scheduler = new PackageInstallationScheduler($this->parameters['packages']);
		
		try {
			$scheduler->buildPackageInstallationStack(($queueType == 'install'));
		}
		catch (PackageUpdateUnauthorizedException $e) {
			return array(
				'template' => $e->getRenderedTemplate()
			);
		}
		
		// validate exclusions
		if ($queueType == 'update') {
			$excludedPackages = $scheduler->getExcludedPackages();
			
			if (!empty($excludedPackages)) {
				return array(
					'excludedPackages' => true,
					'template' => WCF::getTPL()->fetch('packageUpdateExcludedPackages', 'wcf', array('excludedPackages' => $excludedPackages))
				);
			}
		}
		
		$stack = $scheduler->getPackageInstallationStack();
		$queueID = null;
		if (!empty($stack)) {
			$parentQueueID = 0;
			$processNo = PackageInstallationQueue::getNewProcessNo();
			foreach ($stack as $package) {
				$queue = PackageInstallationQueueEditor::create(array(
					'parentQueueID' => $parentQueueID,
					'processNo' => $processNo,
					'userID' => WCF::getUser()->userID,
					'package' => $package['package'],
					'packageName' => $package['packageName'],
					'packageID' => ($package['packageID'] ?: null),
					'archive' => $package['archive'],
					'action' => $package['action']
				));
				$parentQueueID = $queue->queueID;
				
				if ($queueID === null) {
					$queueID = $queue->queueID;
				}
			}
		}
		
		return array(
			'queueID' => $queueID
		);
	}
}
