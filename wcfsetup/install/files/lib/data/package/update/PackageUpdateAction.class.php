<?php
namespace wcf\data\package\update;
use wcf\data\package\update\version\PackageUpdateVersion;
use wcf\data\search\Search;
use wcf\data\search\SearchEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes package update-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 */
class PackageUpdateAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\update\PackageUpdateEditor';
	
	/**
	 * search object
	 * @var	wcf\data\search\Search
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
		$conditions = new PreparedStatementConditionBuilder();
		if (!empty($this->parameters['package'])) {
			$conditions->add("package_update.package LIKE ?", array('%'.$this->parameters['package'].'%'));
		}
		if (!empty($this->parameters['packageDescription'])) {
			$conditions->add("package_update.packageDescription LIKE ?", array('%'.$this->parameters['packageDescription'].'%'));
		}
		if (!empty($this->parameters['packageName'])) {
			$conditions->add("package_update.packageName LIKE ?", array('%'.$this->parameters['packageName'].'%'));
		}
		
		// find matching packages
		$sql = "SELECT		package_update.packageUpdateID
			FROM		wcf".WCF_N."_package_update package_update
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
				$packageVersions[$package][$packageUpdateID]['accessible'][$row['packageUpdateVersionID']] = $row['packageVersion'];
			}
			$packageVersions[$package][$packageUpdateID]['existing'][$row['packageUpdateVersionID']] = $row['packageVersion'];
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
			
			uksort($accessible, array('wcf\data\package\Package', 'compareVersion'));
			uksort($existing, array('wcf\data\package\Package', 'compareVersion'));
			
			$accessible = array_pop($accessible);
			$existing = array_pop($existing);
			$packageUpdates[$versions[$accessible]] = array(
				'accessible' => $accessible,
				'existing' => $existing
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
		
		$sql = "SELECT	packageUpdateID, packageName, packageDescription, author, authorURL
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
}
