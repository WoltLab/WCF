<?php
namespace wcf\system\package;
use wcf\data\package\Package;
use wcf\data\package\update\PackageUpdateEditor;
use wcf\data\package\update\PackageUpdateList;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\data\package\update\version\PackageUpdateVersionEditor;
use wcf\data\package\update\version\PackageUpdateVersionList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Provides functions to manage package updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
abstract class PackageUpdateDispatcher {
	/**
	 * Refreshes the package database.
	 *
	 * @param	array		$packageUpdateServerIDs
	 */
	public static function refreshPackageDatabase(array $packageUpdateServerIDs = array()) {
		// get update server data
		$updateServers = PackageUpdateServer::getActiveUpdateServers($packageUpdateServerIDs);
		
		// loop servers
		foreach ($updateServers as $updateServer) {
			if ($updateServer->lastUpdateTime < TIME_NOW - 600) {
				try {
					self::getPackageUpdateXML($updateServer);
				}
				catch (SystemException $e) {
					// save error status
					$updateServerEditor = new PackageUpdateServerEditor($updateServer);
					$updateServerEditor->update(array(
						'status' => 'offline',
						'errorMessage' => $e->getMessage()
					));
				}
			}
		}
	}
	
	/**
	 * Gets the package_update.xml from an update server.
	 * 
	 * @param	wcf\data\package\update\server\PackageUpdateServer	$updateServer
	 */
	protected static function getPackageUpdateXML(PackageUpdateServer $updateServer) {
		// send request
		$response = self::sendRequest($updateServer->serverURL, array('lastUpdateTime' => $updateServer->lastUpdateTime), $updateServer->getAuthData());
		
		// check response
		// check http code
		if ($response['httpStatusCode'] == 401) {
			throw new PackageUpdateAuthorizationRequiredException($updateServer['packageUpdateServerID'], $updateServer['server'], $response);
		}
		
		if ($response['httpStatusCode'] != 200) {
			throw new SystemException(WCF::getLanguage()->get('wcf.acp.packageUpdate.error.listNotFound') . ' ('.$response['httpStatusLine'].')');
		}
		
		// parse given package update xml
		$allNewPackages = self::parsePackageUpdateXML($response['content']);
		unset($response);
		
		// save packages
		if (count($allNewPackages)) {
			self::savePackageUpdates($allNewPackages, $updateServer->packageUpdateServerID);
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
	 * Sends a request to a remote (update) server.
	 * 
	 * @param	string		$url
	 * @param	array		$values
	 * @param	array		$authData
	 * @return	array		$response
	 */
	public static function sendRequest($url, array $values = array(), array $authData = array()) {
		// default values
		$host = '';
		$path = '/';
		$port = 80;
		$postString = '';
		
		// parse url
		$parsedURL = parse_url($url);
		if (!empty($parsedURL['host'])) $host = $parsedURL['host'];
		if (!empty($parsedURL['path'])) $path = $parsedURL['path'];
		if (!empty($parsedURL['query'])) $postString = $parsedURL['query'];
		if (!empty($parsedURL['port'])) $port = $parsedURL['port'];
		
		// connect to server
		if (PROXY_SERVER_HTTP) {
			$parsedProxyURL = parse_url(PROXY_SERVER_HTTP);
			$remoteFile = new RemoteFile($parsedProxyURL['host'], $parsedProxyURL['port'], 30);
			$path = $url;
			$host = $parsedProxyURL['host'];
		}
		else {
			$remoteFile = new RemoteFile($host, $port, 30);
		}
		
		// Build and send the http request
		$request = "POST ".$path." HTTP/1.0\r\n";
		if (isset($authData['authType'])) {
			$request .= "Authorization: Basic ".base64_encode($authData['loginUsername'].":".$authData['loginPassword'])."\r\n";
		}
		
		$request .= "User-Agent: HTTP.PHP (PackageUpdateDispatcher.class.php; WoltLab Community Framework/".WCF_VERSION."; ".WCF::getLanguage()->languageCode.")\r\n";
		$request .= "Accept: */*\r\n";
		$request .= "Accept-Language: ".WCF::getLanguage()->languageCode."\r\n";
		$request .= "Host: ".$host."\r\n";
		
		// build post string
		foreach ($values as $name => $value) {
			if (!empty($postString)) $postString .= '&';
			$postString .= $name.'='.$value;
		}
		
		// send content type and length
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
	   	$request .= "Content-Length: ".strlen($postString)."\r\n";
	   	// if it is a POST request, there MUST be a blank line before the POST data, but there MUST NOT be 
	   	// another blank line before, and of course there must be another blank line at the end of the request!
	   	$request .= "\r\n";
	   	if (!empty($postString)) $request .= $postString."\r\n";
		// send close
	   	$request .= "Connection: Close\r\n\r\n";

	   	// send request
	   	$remoteFile->puts($request);
	   	unset($request, $postString);
	   	
	   	// define response vars
	   	$header = $content = '';
		
		// fetch the response.
		while (!$remoteFile->eof()) {
			$line = $remoteFile->gets();
			if (rtrim($line) != '') {
				$header .= $line;
			} else {
				break;
			}
		}
		while (!$remoteFile->eof()) {
			$content .= $remoteFile->gets();
		}
		
		// clean up and return the server's response.
		$remoteFile->close();
		
		// get http status code / line
		$httpStatusCode = 0;
		$httpStatusLine = '';
		if (preg_match('%http/\d\.\d (\d{3})[^\n]*%i', $header, $match)) {
			$httpStatusLine = trim($match[0]);
			$httpStatusCode = $match[1];
		}
		
		// catch http 301 Moved Permanently
		// catch http 302 Found
		// catch http 303 See Other
		if ($httpStatusCode == 301 || $httpStatusCode == 302 || $httpStatusCode == 303) {
			// find location
			if (preg_match('/location:([^\n]*)/i', $header, $match)) {
				$location = trim($match[1]);
				if ($location != $url) {
					return self::sendRequest($location, $values, $authData);
				}
			}
		}
		// catch other http codes here
		
		return array(
			'httpStatusLine' => $httpStatusLine,
			'httpStatusCode' => $httpStatusCode,
			'header' => $header,
			'content' => $content
		);
	}
	
	/**
	 * Parses a stream containing info from a packages_update.xml.
	 *
	 * @param	string		$content
	 * @return	array		$allNewPackages
	 */
	protected static function parsePackageUpdateXML($content) {
		// load xml document
		$xml = new XML();
		$xml->loadXML('packageUpdateServer.xml', $content);
		$xpath = $xml->xpath();
		
		// loop through <package> tags inside the <section> tag.
		$allNewPackages = array();
		$packages = $xpath->query('/ns:section[@name=\'packages\']/ns:package');
		foreach ($packages as $package) {
			if (!Package::isValidPackageName($package->getAttribute('name'))) {
				throw new SystemException("'".$package->getAttribute('name')."' is not a valid package name.");
			}
			
			$allNewPackages[$package->getAttribute('name')] = self::parsePackageUpdateXMLBlock($xpath, $package);
		}
		
		return $allNewPackages;
	}
	
	/**
	 * Parses the xml stucture from a packages_update.xml.
	 *
	 * @param	\DOMXPath	$xpath
	 * @param	\DOMNode	$package
	 */
	protected static function parsePackageUpdateXMLBlock(\DOMXPath $xpath, \DOMNode $package) {
		// define default values
		$packageInfo = array(
			'packageDescription' => '',
			'isApplication' => 0,
			'plugin' => '',
			'author' => '',
			'authorURL' => '',
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
				
				case 'plugin':
					$packageInfo['plugin'] = $element->nodeValue;
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
			
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				switch ($child->tagName) {
					case 'fromversions':
						$fromversions = $xpath->query('child::*', $child);
						foreach ($fromversions as $fromversion) {
							$packageInfo['versions'][$versionNo]['fromversions'][] = $fromversion->nodeValue;
						}
					break;
					
					case 'updatetype':
						$packageInfo['versions'][$versionNo]['updateType'] = $child->nodeValue;
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
					
					case 'excludedpackages':
						$excludedpackages = $xpath->query('child::*', $child);
						foreach ($excludedpackages as $excludedpackage) {
							$exclusion = $excludedpackage->nodeValue;
							$version = $excludedpackage->getAttribute('version');
							
							$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion] = array();
							if (!empty($version)) {
								$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion]['version'] = $version;
							}
						}
					break;
				}
			}
		}
		
		return $packageInfo;
	}
	
	/**
	 * Updates information parsed from a packages_update.xml into the database.
	 * 
	 * @param 	array		$allNewPackages
	 * @param	integer		$packageUpdateServerID
	 */
	protected static function savePackageUpdates(array &$allNewPackages, $packageUpdateServerID) {
		// find existing packages and delete them
		// get existing packages
		$existingPackages = array();
		$packageUpdateList = new PackageUpdateList();
		$packageUpdateList->getConditionBuilder()->add("package_update.packageUpdateServerID = ? AND package_update.package IN (?)", array($packageUpdateServerID, array_keys($allNewPackages)));
		$packageUpdateList->sqlLimit = 0;
		$packageUpdateList->readObjects();
		$tmp = $packageUpdateList->getObjects();
		
		foreach ($tmp as $packageUpdate) {
			$existingPackages[$packageUpdate->package] = $packageUpdate;
		}
		
		// get existing versions
		$existingPackageVersions = array();
		if (count($existingPackages) > 0) {
			// get package update ids
			$packageUpdateIDs = array();
			foreach ($existingPackages as $packageUpdate) {
				$packageUpdateIDs[] = $packageUpdate->packageUpdateID;
			}
			
			// get version list
			$versionList = new PackageUpdateVersionList();
			$versionList->getConditionBuilder()->add("package_update_version.packageUpdateID IN (?)", array($packageUpdateIDs));
			$versionList->sqlLimit = 0;
			$versionList->readObjects();
			$tmp = $versionList->getObjects();
			
			foreach ($tmp as $version) {
				if (!isset($existingPackageVersions[$version->packageUpdateID])) $existingPackageVersions[$version->packageUpdateID] = array();
				$existingPackageVersions[$version->packageUpdateID][$version->packageVersion] = $version;
			}
		}
		
		// insert updates
		$excludedPackagesParameters = $fromversionParameters = $insertParameters = array();
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
					'isApplication' => $packageData['isApplication'],
					'plugin' => $packageData['plugin']
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
					'isApplication' => $packageData['isapplication'],
					'plugin' => $packageData['plugin']
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
							'updateType' => $versionData['updateType'],
							'packageDate' => $versionData['packageDate'],
							'filename' => $packageFile
						));
					}
					else {
						// create new database entry
						$version = PackageUpdateVersionEditor::create(array(
							'packageUpdateID' => $packageUpdateID,
							'packageVersion' => $packageVersion,
							'updateType' => $versionData['updateType'],
							'packageDate' => $versionData['packageDate'],
							'filename' => $packageFile
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
			foreach ($requirementInserts as $requirement) {
				$statement->execute(array(
					$requirement['packageUpdateVersionID'],
					$requirement['package'],
					$requirement['minversion']
				));
			}
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
			foreach ($excludedPackagesParameters as $excludedPackage) {
				$statement->execute(array(
					$excludedPackage['packageUpdateVersionID'],
					$excludedPackage['excludedPackage'],
					$excludedPackage['excludedPackageVersion']
				));
			}
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
			foreach ($fromversionInserts as $fromversion) {
				$statement->execute(array(
					$fromversion['packageUpdateVersionID'],
					$fromversion['fromversion']
				));
			}
		}
	}
	
	/**
	 * Returns a list of available updates for installed packages.
	 * 
	 * @param	boolean		$removeRequirements
	 * @return 	array
	 */
	public static function getAvailableUpdates($removeRequirements = true) {
		$updates = array();
		
		// get update server data
		$updateServers = PackageUpdateServer::getActiveUpdateServers();
		$packageUpdateServerIDs = array_keys($updateServers);
		if (empty($packageUpdateServerIDs)) return $updates;
		
		// get existing packages and their versions
		$existingPackages = array();
		$sql = "SELECT	packageID, package, instanceNo, packageDescription,
				packageVersion, packageDate, author, authorURL, isApplication,
				CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
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
					puv.packageUpdateVersionID, puv.updateType, puv.packageDate, puv.filename, puv.packageVersion
			FROM		wcf".WCF_N."_package_update pu
			LEFT JOIN	wcf".WCF_N."_package_update_version puv
			ON		(puv.packageUpdateID = pu.packageUpdateID)
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
							'updateType' => $row['updateType'],
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
			uksort($updates[$packageID]['versions'], array('Package', 'compareVersion'));
			$updates[$packageID]['version'] = end($updates[$packageID]['versions']);
		}
		
		// remove requirements of application packages
		if ($removeRequirements) {
			foreach ($existingPackages as $identifier => $instances) {
				foreach ($instances as $instance) {
					if ($instance['isApplication'] && isset($updates[$instance['packageID']])) {
						$updates = self::removeUpdateRequirements($updates, $updates[$instance['packageID']]['version']['servers'][0]['packageUpdateVersionID']);
					}
				}
			}
		}
		
		return $updates;
	}
	
	/**
	 * Removes unnecessary updates of requirements from the list of available updates.
	 * 
	 * @param	array		$updates
	 * @param 	integer		$packageUpdateVersionID
	 * @return	array		$updates
	 */
	protected static function removeUpdateRequirements(array $updates, $packageUpdateVersionID) {
		$sql = "SELECT		pur.package, pur.minversion, p.packageID
			FROM		wcf".WCF_N."_package_update_requirement pur
			LEFT JOIN	wcf".WCF_N."_package p
			ON		(p.package = pur.package)
			WHERE		pur.packageUpdateVersionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageUpdateVersionID));
		while ($row = $statement->fetchArray()) {
			if (isset($updates[$row['packageID']])) {
				$updates = self::removeUpdateRequirements($updates, $updates[$row['packageID']]['version']['servers'][0]['packageUpdateVersionID']);
				if (Package::compareVersion($row['minversion'], $updates[$row['packageID']]['version']['packageVersion'], '>=')) {
					unset($updates[$row['packageID']]);
				}
			}
		}
		
		return $updates;
	}
	
	public static function prepareInstallation(array $selectedPackages, array $packageUpdateServerIDs = array(), $download = true) {
		return new PackageInstallationScheduler($selectedPackages, $packageUpdateServerIDs, $download);
	}
	
	/**
	 * Gets package update versions of a package.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @return	array		package update versions
	 */
	public static function getPackageUpdateVersions($package, $version = '') {
		// get newest package version
		if (empty($version)) {
			$version = self::getNewestPackageVersion($package);
		}
		
		// get versions
		$versions = array();
		$sql = "SELECT		puv.*, pu.*
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
		
		if (!count($versions)) {
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
	public static function getNewestPackageVersion($package) {
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
		usort($versions, array('Package', 'compareVersion'));
		
		// take newest (last)
		return array_pop($versions);
	}
	
	/**
	 * Stores the filename of a download in session.
	 * 
	 * @param 	string		$package	package identifier
	 * @param 	string		$version	package version
	 * @param 	string		$filename
	 */
	public static function cacheDownload($package, $version, $filename) {
		$cachedDownloads = WCF::getSession()->getVar('cachedPackageUpdateDownloads');
		if (!is_array($cachedDownloads)) {
			$cachedDownloads = array();
		}
		
		// store in session
		$cachedDownloads[$package.'@'.$version] = $filename;
		WCF::getSession()->register('cachedPackageUpdateDownloads', $cachedDownloads);
	}
}
