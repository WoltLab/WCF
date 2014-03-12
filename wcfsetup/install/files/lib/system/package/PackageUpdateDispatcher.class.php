<?php
namespace wcf\system\package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\data\package\update\version\PackageUpdateVersionEditor;
use wcf\data\package\update\version\PackageUpdateVersionList;
use wcf\data\package\update\PackageUpdateEditor;
use wcf\data\package\update\PackageUpdateList;
use wcf\data\package\Package;
use wcf\system\cache\builder\PackageUpdateCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageUpdateUnauthorizedException;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\XML;

/**
 * Provides functions to manage package updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class PackageUpdateDispatcher extends SingletonFactory {
	/**
	 * Refreshes the package database.
	 * 
	 * @param	array<integer>		$packageUpdateServerIDs
	 * @param	boolean			$ignoreCache
	 */
	public function refreshPackageDatabase(array $packageUpdateServerIDs = array(), $ignoreCache = false) {
		// get update server data
		$updateServers = PackageUpdateServer::getActiveUpdateServers($packageUpdateServerIDs);
		
		// loop servers
		$refreshedPackageLists = false;
		foreach ($updateServers as $updateServer) {
			if ($ignoreCache || $updateServer->lastUpdateTime < TIME_NOW - 600) {
				$errorMessage = '';
				
				try {
					$this->getPackageUpdateXML($updateServer);
					$refreshedPackageLists = true;
				}
				catch (SystemException $e) {
					$errorMessage = $e->getMessage();
				}
				catch (PackageUpdateUnauthorizedException $e) {
					$reply = $e->getRequest()->getReply();
					foreach ($reply['headers'] as $header) {
						if (preg_match('~^HTTP~', $header)) {
							$errorMessage = $header;
							break;
						}
					}
					
					if (!$errorMessage) {
						$errorMessage = 'Unknown (HTTP status ' . (is_array($reply['statusCode']) ? reset($reply['statusCode']) : $reply['statusCode']) . ')';
					}
				}
				
				if ($errorMessage) {
					// save error status
					$updateServerEditor = new PackageUpdateServerEditor($updateServer);
					$updateServerEditor->update(array(
						'status' => 'offline',
						'errorMessage' => $errorMessage
					));
				}
			}
		}
		
		if ($refreshedPackageLists) {
			PackageUpdateCacheBuilder::getInstance()->reset();
		}
	}
	
	/**
	 * Gets the package_update.xml from an update server.
	 * 
	 * @param	\wcf\data\package\update\server\PackageUpdateServer	$updateServer
	 */
	protected function getPackageUpdateXML(PackageUpdateServer $updateServer) {
		$authData = $updateServer->getAuthData();
		$settings = array();
		if ($authData) $settings['auth'] = $authData;
		
		$postData = array(
			'lastUpdateTime' => $updateServer->lastUpdateTime
		);
		
		// append auth code if set and update server resolves to woltlab.com
		if (PACKAGE_SERVER_AUTH_CODE && Regex::compile('^https?://[a-z]+.woltlab.com/')->match($updateServer->serverURL)) {
			$postData['authCode'] = PACKAGE_SERVER_AUTH_CODE;
		}
		
		$request = new HTTPRequest($updateServer->serverURL, $settings, $postData);
		
		try {
			$request->execute();
			$reply = $request->getReply();
		}
		catch (HTTPUnauthorizedException $e) {
			throw new PackageUpdateUnauthorizedException($request, $updateServer);
		}
		catch (SystemException $e) {
			$reply = $request->getReply();
			
			$statusCode = (is_array($reply['statusCode'])) ? reset($reply['statusCode']) : $reply['statusCode'];
			throw new SystemException(WCF::getLanguage()->get('wcf.acp.package.update.error.listNotFound') . ' ('.$statusCode.')');
		}
		
		// parse given package update xml
		$allNewPackages = $this->parsePackageUpdateXML($reply['body']);
		unset($request, $reply);
		
		// save packages
		if (!empty($allNewPackages)) {
			$this->savePackageUpdates($allNewPackages, $updateServer->packageUpdateServerID);
		}
		unset($allNewPackages);
		
		// update server status
		$updateServerEditor = new PackageUpdateServerEditor($updateServer);
		$updateServerEditor->update(array(
			'lastUpdateTime' => TIME_NOW,
			'status' => 'online',
			'errorMessage' => ''
		));
	}
	
	/**
	 * Parses a stream containing info from a packages_update.xml.
	 * 
	 * @param	string		$content
	 * @return	array		$allNewPackages
	 */
	protected function parsePackageUpdateXML($content) {
		// load xml document
		$xml = new XML();
		$xml->loadXML('packageUpdateServer.xml', $content);
		$xpath = $xml->xpath();
		
		// loop through <package> tags inside the <section> tag.
		$allNewPackages = array();
		$packages = $xpath->query('/ns:section/ns:package');
		foreach ($packages as $package) {
			if (!Package::isValidPackageName($package->getAttribute('name'))) {
				throw new SystemException("'".$package->getAttribute('name')."' is not a valid package name.");
			}
			
			$allNewPackages[$package->getAttribute('name')] = $this->parsePackageUpdateXMLBlock($xpath, $package);
		}
		
		return $allNewPackages;
	}
	
	/**
	 * Parses the xml stucture from a packages_update.xml.
	 * 
	 * @param	\DOMXPath	$xpath
	 * @param	\DOMNode	$package
	 */
	protected function parsePackageUpdateXMLBlock(\DOMXPath $xpath, \DOMNode $package) {
		// define default values
		$packageInfo = array(
			'author' => '',
			'authorURL' => '',
			'isApplication' => 0,
			'packageDescription' => '',
			'versions' => array()
		);
		
		// parse package information
		$elements = $xpath->query('./ns:packageinformation/*', $package);
		foreach ($elements as $element) {
			switch ($element->tagName) {
				case 'packagename':
					$packageInfo['packageName'] = $element->nodeValue;
				break;
				
				case 'packagedescription':
					$packageInfo['packageDescription'] = $element->nodeValue;
				break;
				
				case 'isapplication':
					$packageInfo['isApplication'] = intval($element->nodeValue);
				break;
			}
		}
		
		// parse author information
		$elements = $xpath->query('./ns:authorinformation/*', $package);
		foreach ($elements as $element) {
			switch ($element->tagName) {
				case 'author':
					$packageInfo['author'] = $element->nodeValue;
				break;
				
				case 'authorurl':
					$packageInfo['authorURL'] = $element->nodeValue;
				break;
			}
		}
		
		// parse versions
		$elements = $xpath->query('./ns:versions/ns:version', $package);
		foreach ($elements as $element) {
			$versionNo = $element->getAttribute('name');
			$packageInfo['versions'][$versionNo] = array(
				'isAccessible' => ($element->getAttribute('accessible') == 'true' ? true : false),
				'isCritical' => ($element->getAttribute('critical') == 'true' ? true : false)
			);
			
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				switch ($child->tagName) {
					case 'fromversions':
						$fromversions = $xpath->query('child::*', $child);
						foreach ($fromversions as $fromversion) {
							$packageInfo['versions'][$versionNo]['fromversions'][] = $fromversion->nodeValue;
						}
					break;
					
					case 'timestamp':
						$packageInfo['versions'][$versionNo]['packageDate'] = $child->nodeValue;
					break;
					
					case 'file':
						$packageInfo['versions'][$versionNo]['file'] = $child->nodeValue;
					break;
					
					case 'requiredpackages':
						$requiredPackages = $xpath->query('child::*', $child);
						foreach ($requiredPackages as $requiredPackage) {
							$minVersion = $requiredPackage->getAttribute('minversion');
							$required = $requiredPackage->nodeValue;
							
							$packageInfo['versions'][$versionNo]['requiredPackages'][$required] = array();
							if (!empty($minVersion)) {
								$packageInfo['versions'][$versionNo]['requiredPackages'][$required]['minversion'] = $minVersion;
							}
						}
					break;
					
					case 'optionalpackages':
						$packageInfo['versions'][$versionNo]['optionalPackages'] = array();
						
						$optionalPackages = $xpath->query('child::*', $child);
						foreach ($optionalPackages as $optionalPackage) {
							$packageInfo['versions'][$versionNo]['optionalPackages'][] = $optionalPackage->nodeValue;
						}
					break;
					
					case 'excludedpackages':
						$excludedpackages = $xpath->query('child::*', $child);
						foreach ($excludedpackages as $excludedPackage) {
							$exclusion = $excludedPackage->nodeValue;
							$version = $excludedPackage->getAttribute('version');
							
							$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion] = array();
							if (!empty($version)) {
								$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion]['version'] = $version;
							}
						}
					break;
					
					case 'license':
						$packageInfo['versions'][$versionNo]['license'] = array(
							'license' => $child->nodeValue,
							'licenseURL' => ($child->hasAttribute('url') ? $child->getAttribute('url') : '')
						);
					break;
				}
			}
		}
		
		return $packageInfo;
	}
	
	/**
	 * Updates information parsed from a packages_update.xml into the database.
	 * 
	 * @param	array		$allNewPackages
	 * @param	integer		$packageUpdateServerID
	 */
	protected function savePackageUpdates(array &$allNewPackages, $packageUpdateServerID) {
		// find existing packages and delete them
		// get existing packages
		$existingPackages = array();
		$packageUpdateList = new PackageUpdateList();
		$packageUpdateList->getConditionBuilder()->add("package_update.packageUpdateServerID = ? AND package_update.package IN (?)", array($packageUpdateServerID, array_keys($allNewPackages)));
		$packageUpdateList->readObjects();
		$tmp = $packageUpdateList->getObjects();
		
		foreach ($tmp as $packageUpdate) {
			$existingPackages[$packageUpdate->package] = $packageUpdate;
		}
		
		// get existing versions
		$existingPackageVersions = array();
		if (!empty($existingPackages)) {
			// get package update ids
			$packageUpdateIDs = array();
			foreach ($existingPackages as $packageUpdate) {
				$packageUpdateIDs[] = $packageUpdate->packageUpdateID;
			}
			
			// get version list
			$versionList = new PackageUpdateVersionList();
			$versionList->getConditionBuilder()->add("package_update_version.packageUpdateID IN (?)", array($packageUpdateIDs));
			$versionList->readObjects();
			$tmp = $versionList->getObjects();
			
			foreach ($tmp as $version) {
				if (!isset($existingPackageVersions[$version->packageUpdateID])) $existingPackageVersions[$version->packageUpdateID] = array();
				$existingPackageVersions[$version->packageUpdateID][$version->packageVersion] = $version;
			}
		}
		
		// insert updates
		$excludedPackagesParameters = $fromversionParameters = $insertParameters = $optionalInserts = $requirementInserts = array();
		foreach ($allNewPackages as $identifier => $packageData) {
			if (isset($existingPackages[$identifier])) {
				$packageUpdateID = $existingPackages[$identifier]->packageUpdateID;
				
				// update database entry
				$packageUpdateEditor = new PackageUpdateEditor($existingPackages[$identifier]);
				$packageUpdateEditor->update(array(
					'packageName' => $packageData['packageName'],
					'packageDescription' => $packageData['packageDescription'],
					'author' => $packageData['author'],
					'authorURL' => $packageData['authorURL'],
					'isApplication' => $packageData['isApplication']
				));
			}
			else {
				// create new database entry
				$packageUpdate = PackageUpdateEditor::create(array(
					'packageUpdateServerID' => $packageUpdateServerID,
					'package' => $identifier,
					'packageName' => $packageData['packageName'],
					'packageDescription' => $packageData['packageDescription'],
					'author' => $packageData['author'],
					'authorURL' => $packageData['authorURL'],
					'isApplication' => $packageData['isApplication']
				));
				
				$packageUpdateID = $packageUpdate->packageUpdateID;
			}
			
			// register version(s) of this update package.
			if (isset($packageData['versions'])) {
				foreach ($packageData['versions'] as $packageVersion => $versionData) {
					if (isset($versionData['file'])) $packageFile = $versionData['file'];
					else $packageFile = '';
					
					if (isset($existingPackageVersions[$packageUpdateID]) && isset($existingPackageVersions[$packageUpdateID][$packageVersion])) {
						$packageUpdateVersionID = $existingPackageVersions[$packageUpdateID][$packageVersion]->packageUpdateVersionID;
						
						// update database entry
						$versionEditor = new PackageUpdateVersionEditor($existingPackageVersions[$packageUpdateID][$packageVersion]);
						$versionEditor->update(array(
							'filename' => $packageFile,
							'isAccessible' => ($versionData['isAccessible'] ? 1 : 0),
							'isCritical' => ($versionData['isCritical'] ? 1 : 0),
							'license' => (isset($versionData['license']['license']) ? $versionData['license']['license'] : ''),
							'licenseURL' => (isset($versionData['license']['license']) ? $versionData['license']['licenseURL'] : ''),
							'packageDate' => $versionData['packageDate']
						));
					}
					else {
						// create new database entry
						$version = PackageUpdateVersionEditor::create(array(
							'filename' => $packageFile,
							'license' => (isset($versionData['license']['license']) ? $versionData['license']['license'] : ''),
							'licenseURL' => (isset($versionData['license']['license']) ? $versionData['license']['licenseURL'] : ''),
							'isAccessible' => ($versionData['isAccessible'] ? 1 : 0),
							'isCritical' => ($versionData['isCritical'] ? 1 : 0),
							'packageDate' => $versionData['packageDate'],
							'packageUpdateID' => $packageUpdateID,
							'packageVersion' => $packageVersion
						));
						
						$packageUpdateVersionID = $version->packageUpdateVersionID;
					}
					
					// register requirement(s) of this update package version.
					if (isset($versionData['requiredPackages'])) {
						foreach ($versionData['requiredPackages'] as $requiredIdentifier => $required) {
							$requirementInserts[] = array(
								'packageUpdateVersionID' => $packageUpdateVersionID,
								'package' => $requiredIdentifier,
								'minversion' => (isset($required['minversion']) ? $required['minversion'] : '')
							);
						}
					}
					
					// register optional packages of this update package version
					if (isset($versionData['optionalPackages'])) {
						foreach ($versionData['optionalPackages'] as $optionalPackage) {
							$optionalInserts[] = array(
								'packageUpdateVersionID' => $packageUpdateVersionID,
								'package' => $optionalPackage
							);
						}
					}
					
					// register excluded packages of this update package version.
					if (isset($versionData['excludedPackages'])) {
						foreach ($versionData['excludedPackages'] as $excludedIdentifier => $exclusion) {
							$excludedPackagesParameters[] = array(
								'packageUpdateVersionID' => $packageUpdateVersionID,
								'excludedPackage' => $excludedIdentifier,
								'excludedPackageVersion' => (isset($exclusion['version']) ? $exclusion['version'] : '')
							);
						}
					}
					
					// register fromversions of this update package version.
					if (isset($versionData['fromversions'])) {
						foreach ($versionData['fromversions'] as $fromversion) {
							$fromversionInserts[] = array(
								'packageUpdateVersionID' => $packageUpdateVersionID,
								'fromversion' => $fromversion
							);
						}
					}
				}
			}
		}
		
		// save requirements, excluded packages and fromversions
		// use multiple inserts to save some queries
		if (!empty($requirementInserts)) {
			// clear records
			$sql = "DELETE pur FROM	wcf".WCF_N."_package_update_requirement pur
				LEFT JOIN	wcf".WCF_N."_package_update_version puv
				ON		(puv.packageUpdateVersionID = pur.packageUpdateVersionID)
				LEFT JOIN	wcf".WCF_N."_package_update pu
				ON		(pu.packageUpdateID = puv.packageUpdateID)
				WHERE		pu.packageUpdateServerID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageUpdateServerID));
			
			// insert requirements
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_requirement
						(packageUpdateVersionID, package, minversion)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($requirementInserts as $requirement) {
				$statement->execute(array(
					$requirement['packageUpdateVersionID'],
					$requirement['package'],
					$requirement['minversion']
				));
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($optionalInserts)) {
			// clear records
			$sql = "DELETE puo FROM	wcf".WCF_N."_package_update_optional puo
				LEFT JOIN	wcf".WCF_N."_package_update_version puv
				ON		(puv.packageUpdateVersionID = puo.packageUpdateVersionID)
				LEFT JOIN	wcf".WCF_N."_package_update pu
				ON		(pu.packageUpdateID = puv.packageUpdateID)
				WHERE		pu.packageUpdateServerID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageUpdateServerID));
				
			// insert requirements
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_optional
						(packageUpdateVersionID, package)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($optionalInserts as $requirement) {
				$statement->execute(array(
					$requirement['packageUpdateVersionID'],
					$requirement['package']
				));
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($excludedPackagesParameters)) {
			// clear records
			$sql = "DELETE pue FROM	wcf".WCF_N."_package_update_exclusion pue
				LEFT JOIN	wcf".WCF_N."_package_update_version puv
				ON		(puv.packageUpdateVersionID = pue.packageUpdateVersionID)
				LEFT JOIN	wcf".WCF_N."_package_update pu
				ON		(pu.packageUpdateID = puv.packageUpdateID)
				WHERE		pu.packageUpdateServerID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageUpdateServerID));
			
			// insert excludes
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_exclusion
						(packageUpdateVersionID, excludedPackage, excludedPackageVersion)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($excludedPackagesParameters as $excludedPackage) {
				$statement->execute(array(
					$excludedPackage['packageUpdateVersionID'],
					$excludedPackage['excludedPackage'],
					$excludedPackage['excludedPackageVersion']
				));
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($fromversionInserts)) {
			// clear records
			$sql = "DELETE puf FROM	wcf".WCF_N."_package_update_fromversion puf
				LEFT JOIN	wcf".WCF_N."_package_update_version puv
				ON		(puv.packageUpdateVersionID = puf.packageUpdateVersionID)
				LEFT JOIN	wcf".WCF_N."_package_update pu
				ON		(pu.packageUpdateID = puv.packageUpdateID)
				WHERE		pu.packageUpdateServerID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageUpdateServerID));
			
			// insert excludes
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_fromversion
						(packageUpdateVersionID, fromversion)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($fromversionInserts as $fromversion) {
				$statement->execute(array(
					$fromversion['packageUpdateVersionID'],
					$fromversion['fromversion']
				));
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Returns a list of available updates for installed packages.
	 * 
	 * @param	boolean		$removeRequirements
	 * @param	boolean		$removeOlderMinorReleases
	 * @return	array
	 */
	public function getAvailableUpdates($removeRequirements = true, $removeOlderMinorReleases = false) {
		$updates = array();
		
		// get update server data
		$updateServers = PackageUpdateServer::getActiveUpdateServers();
		$packageUpdateServerIDs = array_keys($updateServers);
		if (empty($packageUpdateServerIDs)) return $updates;
		
		// get existing packages and their versions
		$existingPackages = array();
		$sql = "SELECT	packageID, package, packageDescription, packageName,
				packageVersion, packageDate, author, authorURL, isApplication
			FROM	wcf".WCF_N."_package";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$existingPackages[$row['package']][] = $row;
		}
		if (empty($existingPackages)) return $updates;
		
		// get all update versions
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("pu.packageUpdateServerID IN (?)", array($packageUpdateServerIDs));
		$conditions->add("package IN (SELECT DISTINCT package FROM wcf".WCF_N."_package)");
		
		$sql = "SELECT		pu.packageUpdateID, pu.packageUpdateServerID, pu.package,
					puv.packageUpdateVersionID, puv.isCritical, puv.packageDate, puv.filename, puv.packageVersion
			FROM		wcf".WCF_N."_package_update pu
			LEFT JOIN	wcf".WCF_N."_package_update_version puv
			ON		(puv.packageUpdateID = pu.packageUpdateID AND puv.isAccessible = 1)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			// test version
			foreach ($existingPackages[$row['package']] as $existingVersion) {
				if (Package::compareVersion($existingVersion['packageVersion'], $row['packageVersion'], '<')) {
					// package data
					if (!isset($updates[$existingVersion['packageID']])) {
						$existingVersion['versions'] = array();
						$updates[$existingVersion['packageID']] = $existingVersion;
					}
					
					// version data
					if (!isset($updates[$existingVersion['packageID']]['versions'][$row['packageVersion']])) {
						$updates[$existingVersion['packageID']]['versions'][$row['packageVersion']] = array(
							'isCritical' => $row['isCritical'],
							'packageDate' => $row['packageDate'],
							'packageVersion' => $row['packageVersion'],
							'servers' => array()
						);
					}
					
					// server data
					$updates[$existingVersion['packageID']]['versions'][$row['packageVersion']]['servers'][] = array(
						'packageUpdateID' => $row['packageUpdateID'],
						'packageUpdateServerID' => $row['packageUpdateServerID'],
						'packageUpdateVersionID' => $row['packageUpdateVersionID'],
						'filename' => $row['filename']
					);
				}
			}
		}
		
		// sort package versions
		// and remove old versions
		foreach ($updates as $packageID => $data) {
			uksort($updates[$packageID]['versions'], array('wcf\data\package\Package', 'compareVersion'));
			$updates[$packageID]['version'] = end($updates[$packageID]['versions']);
		}
		
		// remove requirements of application packages
		if ($removeRequirements) {
			foreach ($existingPackages as $identifier => $instances) {
				foreach ($instances as $instance) {
					if ($instance['isApplication'] && isset($updates[$instance['packageID']])) {
						$updates = $this->removeUpdateRequirements($updates, $updates[$instance['packageID']]['version']['servers'][0]['packageUpdateVersionID']);
					}
				}
			}
		}
		
		// remove older minor releases from list, e.g. only display 1.0.2, even if 1.0.1 is available
		if ($removeOlderMinorReleases) {
			foreach ($updates as &$updateData) {
				$highestVersions = array();
				foreach ($updateData['versions'] as $versionNumber => $dummy) {
					if (preg_match('~^(\d+\.\d+)\.~', $versionNumber, $matches)) {
						$major = $matches[1];
						if (isset($highestVersions[$major])) {
							if (version_compare($highestVersions[$major], $versionNumber, '<')) {
								// version is newer, discard current version
								unset($updateData['versions'][$highestVersions[$major]]);
								$highestVersions[$major] = $versionNumber;
							}
							else {
								// version is lower, discard
								unset($updateData['versions'][$versionNumber]);
							}
						}
						else {
							$highestVersions[$major] = $versionNumber;
						}
					}
				}
			}
			unset($updateData);
		}
		
		return $updates;
	}
	
	/**
	 * Removes unnecessary updates of requirements from the list of available updates.
	 * 
	 * @param	array		$updates
	 * @param	integer		$packageUpdateVersionID
	 * @return	array		$updates
	 */
	protected function removeUpdateRequirements(array $updates, $packageUpdateVersionID) {
		$sql = "SELECT		pur.package, pur.minversion, p.packageID
			FROM		wcf".WCF_N."_package_update_requirement pur
			LEFT JOIN	wcf".WCF_N."_package p
			ON		(p.package = pur.package)
			WHERE		pur.packageUpdateVersionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageUpdateVersionID));
		while ($row = $statement->fetchArray()) {
			if (isset($updates[$row['packageID']])) {
				$updates = $this->removeUpdateRequirements($updates, $updates[$row['packageID']]['version']['servers'][0]['packageUpdateVersionID']);
				if (Package::compareVersion($row['minversion'], $updates[$row['packageID']]['version']['packageVersion'], '>=')) {
					unset($updates[$row['packageID']]);
				}
			}
		}
		
		return $updates;
	}
	
	/**
	 * Creates a new package installation scheduler.
	 * 
	 * @param	array			$selectedPackages
	 * @return	\wcf\system\package\PackageInstallationScheduler
	 */
	public function prepareInstallation(array $selectedPackages) {
		return new PackageInstallationScheduler($selectedPackages);
	}
	
	/**
	 * Gets package update versions of a package.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @return	array		package update versions
	 */
	public function getPackageUpdateVersions($package, $version = '') {
		// get newest package version
		if (empty($version)) {
			$version = $this->getNewestPackageVersion($package);
		}
		
		// get versions
		$versions = array();
		$sql = "SELECT		puv.*, pu.*, pus.loginUsername, pus.loginPassword
			FROM		wcf".WCF_N."_package_update_version puv
			LEFT JOIN	wcf".WCF_N."_package_update pu
			ON		(pu.packageUpdateID = puv.packageUpdateID)
			LEFT JOIN	wcf".WCF_N."_package_update_server pus
			ON		(pus.packageUpdateServerID = pu.packageUpdateServerID)
			WHERE		pu.package = ?
					AND puv.packageVersion = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$package,
			$version
		));
		while ($row = $statement->fetchArray()) {
			$versions[] = $row;
		}
		
		if (empty($versions)) {
			throw new SystemException("Can not find package '".$package."' in version '".$version."'");
		}
		
		return $versions;
	}
	
	/**
	 * Returns the newest available version of a package.
	 * 
	 * @param	string		$package	package identifier
	 * @return	string		newest package version
	 */
	public function getNewestPackageVersion($package) {
		// get all versions
		$versions = array();
		$sql = "SELECT	packageVersion
			FROM	wcf".WCF_N."_package_update_version
			WHERE	packageUpdateID IN (
					SELECT	packageUpdateID
					FROM	wcf".WCF_N."_package_update
					WHERE	package = ?
				)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($package));
		while ($row = $statement->fetchArray()) {
			$versions[$row['packageVersion']] = $row['packageVersion'];
		}
		
		// sort by version number
		usort($versions, array('wcf\data\package\Package', 'compareVersion'));
		
		// take newest (last)
		return array_pop($versions);
	}
	
	/**
	 * Stores the filename of a download in session.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @param	string		$filename
	 */
	public function cacheDownload($package, $version, $filename) {
		$cachedDownloads = WCF::getSession()->getVar('cachedPackageUpdateDownloads');
		if (!is_array($cachedDownloads)) {
			$cachedDownloads = array();
		}
		
		// store in session
		$cachedDownloads[$package.'@'.$version] = $filename;
		WCF::getSession()->register('cachedPackageUpdateDownloads', $cachedDownloads);
	}
}
