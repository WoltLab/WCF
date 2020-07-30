<?php
namespace wcf\system\package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\data\package\update\version\PackageUpdateVersionEditor;
use wcf\data\package\update\PackageUpdateEditor;
use wcf\data\package\Package;
use wcf\system\cache\builder\PackageUpdateCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\JSON;
use wcf\util\XML;

/**
 * Provides functions to manage package updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageUpdateDispatcher extends SingletonFactory {
	protected $hasAuthCode = false;
	protected $purchasedVersions = [
		'woltlab' => [],
		'pluginstore' => []
	];
	
	/**
	 * Refreshes the package database.
	 * 
	 * @param	integer[]		$packageUpdateServerIDs
	 * @param	boolean			$ignoreCache
	 */
	public function refreshPackageDatabase(array $packageUpdateServerIDs = [], $ignoreCache = false) {
		// get update server data
		$tmp = PackageUpdateServer::getActiveUpdateServers($packageUpdateServerIDs);
		
		// loop servers
		$updateServers = [];
		$foundWoltLabServer = false;
		$requirePurchasedVersions = false;
		foreach ($tmp as $updateServer) {
			if ($ignoreCache || $updateServer->lastUpdateTime < TIME_NOW - 600) {
				if (preg_match('~^https?://(?:update|store)\.woltlab\.com\/~', $updateServer->serverURL)) {
					$requirePurchasedVersions = true;
					
					// move a woltlab.com update server to the front of the queue to probe for SSL support
					if (!$foundWoltLabServer) {
						array_unshift($updateServers, $updateServer);
						$foundWoltLabServer = true;
						
						continue;
					}
				}
				
				$updateServers[] = $updateServer;
			}
		}
		
		if ($requirePurchasedVersions && PACKAGE_SERVER_AUTH_CODE) {
			$this->getPurchasedVersions();
		}
		
		// loop servers
		$refreshedPackageLists = false;
		foreach ($updateServers as $updateServer) {
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
				list($errorMessage) = reset($reply['httpHeaders']);
			}
			
			if ($errorMessage) {
				// save error status
				$updateServerEditor = new PackageUpdateServerEditor($updateServer);
				$updateServerEditor->update([
					'status' => 'offline',
					'errorMessage' => $errorMessage
				]);
			}
		}
		
		if ($refreshedPackageLists) {
			PackageUpdateCacheBuilder::getInstance()->reset();
		}
	}
	
	protected function getPurchasedVersions() {
		if (!RemoteFile::supportsSSL()) {
			return;
		}
		
		$request = new HTTPRequest(
			'https://api.woltlab.com/1.0/customer/license/list.json',
			['timeout' => 5],
			['authCode' => PACKAGE_SERVER_AUTH_CODE]
		);
		
		try {
			$request->execute();
			$reply = JSON::decode($request->getReply()['body']);
			if ($reply['status'] == 200) {
				$this->hasAuthCode = true;
				$this->purchasedVersions = [
					'woltlab' => (isset($reply['woltlab']) ? $reply['woltlab'] : []),
					'pluginstore' => (isset($reply['pluginstore']) ? $reply['pluginstore'] : [])
				];
			}
		}
		catch (SystemException $e) {
			// ignore
		}
	}
	
	/**
	 * Fetches the package_update.xml from an update server.
	 * 
	 * @param	PackageUpdateServer	$updateServer
	 * @param	boolean			$forceHTTP
	 * @throws	PackageUpdateUnauthorizedException
	 * @throws	SystemException
	 */
	protected function getPackageUpdateXML(PackageUpdateServer $updateServer, $forceHTTP = false) {
		$settings = [];
		$authData = $updateServer->getAuthData();
		if ($authData) $settings['auth'] = $authData;
		
		$secureConnection = $updateServer->attemptSecureConnection();
		if ($secureConnection && !$forceHTTP) $settings['timeout'] = 5;
		
		$request = new HTTPRequest($updateServer->getListURL($forceHTTP), $settings);
		
		$apiVersion = $updateServer->apiVersion;
		if (in_array($apiVersion, ['2.1', '3.1'])) {
			// skip etag check for WoltLab servers when an auth code is provided
			if (!preg_match('~^https?://(?:update|store)\.woltlab\.com\/~', $updateServer->serverURL) || !PACKAGE_SERVER_AUTH_CODE) {
				$metaData = $updateServer->getMetaData();
				if (isset($metaData['list']['etag'])) $request->addHeader('if-none-match', $metaData['list']['etag']);
				if (isset($metaData['list']['lastModified'])) $request->addHeader('if-modified-since', $metaData['list']['lastModified']);
			}
		}
		
		try {
			$request->execute();
			$reply = $request->getReply();
		}
		catch (HTTPUnauthorizedException $e) {
			throw new PackageUpdateUnauthorizedException($request, $updateServer);
		}
		catch (SystemException $e) {
			$reply = $request->getReply();
			
			$statusCode = is_array($reply['statusCode']) ? reset($reply['statusCode']) : $reply['statusCode'];
			// status code 0 is a connection timeout
			if (!$statusCode && $secureConnection) {
				if (preg_match('~https?://(?:update|store)\.woltlab\.com\/~', $updateServer->serverURL)) {
					// woltlab.com servers are most likely to be available, thus we assume that SSL connections are dropped
					RemoteFile::disableSSL();
				}
				
				// retry via http
				$this->getPackageUpdateXML($updateServer, true);
				return;
			}
			
			throw new SystemException(WCF::getLanguage()->get('wcf.acp.package.update.error.listNotFound') . ' ('.$statusCode.')');
		}
		
		$data = [
			'lastUpdateTime' => TIME_NOW,
			'status' => 'online',
			'errorMessage' => ''
		];
		
		// check if server indicates support for a newer API
		if ($updateServer->apiVersion !== '3.1' && !empty($reply['httpHeaders']['wcf-update-server-api'])) {
			$apiVersions = explode(' ', reset($reply['httpHeaders']['wcf-update-server-api']));
			if (in_array('3.1', $apiVersions)) {
				$apiVersion = $data['apiVersion'] = '3.1';
			}
			else if (in_array('2.1', $apiVersions)) {
				$apiVersion = $data['apiVersion'] = '2.1';
			}
		}
		
		// parse given package update xml
		$allNewPackages = false;
		if ($apiVersion === '2.0' || $reply['statusCode'] != 304) {
			$allNewPackages = $this->parsePackageUpdateXML($updateServer, $reply['body'], $apiVersion);
		}
		
		$metaData = [];
		if (in_array($apiVersion, ['2.1', '3.1'])) {
			if (empty($reply['httpHeaders']['etag']) && empty($reply['httpHeaders']['last-modified'])) {
				throw new SystemException("Missing required HTTP headers 'etag' and 'last-modified'.");
			}
			else if (empty($reply['httpHeaders']['wcf-update-server-ssl'])) {
				throw new SystemException("Missing required HTTP header 'wcf-update-server-ssl'.");
			}
			
			$metaData['list'] = [];
			if (!empty($reply['httpHeaders']['etag'])) $metaData['list']['etag'] = reset($reply['httpHeaders']['etag']);
			if (!empty($reply['httpHeaders']['last-modified'])) $metaData['list']['lastModified'] = reset($reply['httpHeaders']['last-modified']);
			
			$metaData['ssl'] = (reset($reply['httpHeaders']['wcf-update-server-ssl']) == 'true') ? true : false;
		}
		$data['metaData'] = serialize($metaData);
		
		unset($request, $reply);
		
		if ($allNewPackages !== false) {
			// purge package list
			$sql = "DELETE FROM	wcf".WCF_N."_package_update
				WHERE		packageUpdateServerID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$updateServer->packageUpdateServerID]);
			
			// save packages
			if (!empty($allNewPackages)) {
				$this->savePackageUpdates($allNewPackages, $updateServer->packageUpdateServerID);
			}
			unset($allNewPackages);
		}
		
		// update server status
		$updateServerEditor = new PackageUpdateServerEditor($updateServer);
		$updateServerEditor->update($data);
	}
	
	/**
	 * Parses a stream containing info from a packages_update.xml.
	 * 
	 * @param       PackageUpdateServer     $updateServer
	 * @param       string                  $content
	 * @param       string                  $apiVersion
	 * @return      array
	 * @throws      SystemException
	 */
	protected function parsePackageUpdateXML(PackageUpdateServer $updateServer, $content, $apiVersion) {
		$isTrustedServer = $updateServer->isTrustedServer();
		
		// load xml document
		$xml = new XML();
		$xml->loadXML('packageUpdateServer.xml', $content);
		$xpath = $xml->xpath();
		
		$allNewPackages = [];
		$packages = $xpath->query('/ns:section/ns:package');
		/** @var \DOMElement $package */
		foreach ($packages as $package) {
			if (!Package::isValidPackageName($package->getAttribute('name'))) {
				throw new SystemException("'".$package->getAttribute('name')."' is not a valid package name.");
			}
			
			$name = $package->getAttribute('name');
			if (strpos($name, 'com.woltlab.') === 0 && !$isTrustedServer) {
				if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
					throw new SystemException("The server '".$updateServer->serverURL."' attempted to provide an official WoltLab package, but is not authorized.");
				}
				
				// silently ignore this package to avoid unexpected errors for regular users
				continue;
			}
			
			$allNewPackages[$name] = $this->parsePackageUpdateXMLBlock($updateServer, $xpath, $package, $apiVersion);
		}
		
		return $allNewPackages;
	}
	
	/**
	 * Parses the xml structure from a packages_update.xml.
	 * 
	 * @param       PackageUpdateServer     $updateServer
	 * @param       \DOMXPath               $xpath
	 * @param       \DOMElement             $package
	 * @param       string                  $apiVersion
	 * @return      array
	 * @throws      PackageValidationException
	 */
	protected function parsePackageUpdateXMLBlock(PackageUpdateServer $updateServer, \DOMXPath $xpath, \DOMElement $package, $apiVersion) {
		// define default values
		$packageInfo = [
			'author' => '',
			'authorURL' => '',
			'isApplication' => 0,
			'packageDescription' => '',
			'versions' => [],
			'pluginStoreFileID' => 0
		];
		
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
				
				case 'pluginStoreFileID':
					if ($updateServer->isWoltLabStoreServer()) {
						$packageInfo['pluginStoreFileID'] = intval($element->nodeValue);
					}
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
		
		$key = '';
		if ($this->hasAuthCode) {
			if ($updateServer->isWoltLabUpdateServer()) $key = 'woltlab';
			else if ($updateServer->isWoltLabStoreServer()) $key = 'pluginstore';
		}
		
		// parse versions
		$elements = $xpath->query('./ns:versions/ns:version', $package);
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$versionNo = $element->getAttribute('name');
			
			$isAccessible = ($element->getAttribute('accessible') == 'true') ? 1 : 0;
			if ($key && $element->getAttribute('requireAuth') == 'true') {
				$packageName = $package->getAttribute('name');
				if (isset($this->purchasedVersions[$key][$packageName])) {
					if ($this->purchasedVersions[$key][$packageName] == '*') {
						$isAccessible = 1;
					}
					else {
						$isAccessible = (Package::compareVersion($versionNo, $this->purchasedVersions[$key][$packageName] . '99', '<=') ? 1 : 0);
					}
				}
				else {
					$isAccessible = 0;
				}
			}
			
			$packageInfo['versions'][$versionNo] = ['isAccessible' => $isAccessible];
			
			$children = $xpath->query('child::*', $element);
			/** @var \DOMElement $child */
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
						
						/** @var \DOMElement $requiredPackage */
						foreach ($requiredPackages as $requiredPackage) {
							$minVersion = $requiredPackage->getAttribute('minversion');
							$required = $requiredPackage->nodeValue;
							
							$packageInfo['versions'][$versionNo]['requiredPackages'][$required] = [];
							if (!empty($minVersion)) {
								$packageInfo['versions'][$versionNo]['requiredPackages'][$required]['minversion'] = $minVersion;
							}
						}
					break;
					
					case 'optionalpackages':
						$packageInfo['versions'][$versionNo]['optionalPackages'] = [];
						
						$optionalPackages = $xpath->query('child::*', $child);
						foreach ($optionalPackages as $optionalPackage) {
							$packageInfo['versions'][$versionNo]['optionalPackages'][] = $optionalPackage->nodeValue;
						}
					break;
					
					case 'excludedpackages':
						$excludedpackages = $xpath->query('child::*', $child);
						/** @var \DOMElement $excludedPackage */
						foreach ($excludedpackages as $excludedPackage) {
							$exclusion = $excludedPackage->nodeValue;
							$version = $excludedPackage->getAttribute('version');
							
							$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion] = [];
							if (!empty($version)) {
								$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion]['version'] = $version;
							}
						}
					break;
					
					case 'license':
						$packageInfo['versions'][$versionNo]['license'] = [
							'license' => $child->nodeValue,
							'licenseURL' => $child->hasAttribute('url') ? $child->getAttribute('url') : ''
						];
					break;
					
					case 'compatibility':
						if ($apiVersion !== '3.1') continue 2;
						
						$packageInfo['versions'][$versionNo]['compatibility'] = [];
						
						/** @var \DOMElement $compatibleVersion */
						foreach ($xpath->query('child::*', $child) as $compatibleVersion) {
							if ($compatibleVersion->nodeName === 'api' && $compatibleVersion->hasAttribute('version')) {
								$versionNumber = $compatibleVersion->getAttribute('version');
								if (!preg_match('~^(?:201[7-9]|20[2-9][0-9])$~', $versionNumber)) {
									if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
										throw new PackageValidationException(PackageValidationException::INVALID_API_VERSION, ['version' => $versionNumber]);
									}
									else {
										continue;
									}
								}
								
								$packageInfo['versions'][$versionNo]['compatibility'][] = $versionNumber;
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
	 * @param	array		$allNewPackages
	 * @param	integer		$packageUpdateServerID
	 */
	protected function savePackageUpdates(array &$allNewPackages, $packageUpdateServerID) {
		$excludedPackagesParameters = $fromversionParameters = $insertParameters = $optionalInserts = $requirementInserts = $compatibilityInserts = [];
		$sql = "INSERT INTO     wcf" . WCF_N . "_package_update
					(packageUpdateServerID, package, packageName, packageDescription, author, authorURL, isApplication, pluginStoreFileID)
			VALUES          (?, ?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($allNewPackages as $identifier => $packageData) {
			$statement->execute([
				$packageUpdateServerID,
				$identifier,
				$packageData['packageName'],
				$packageData['packageDescription'],
				$packageData['author'],
				$packageData['authorURL'],
				$packageData['isApplication'],
				$packageData['pluginStoreFileID'],
			]);
		}
		WCF::getDB()->commitTransaction();
		
		$sql = "SELECT  packageUpdateID, package
			FROM    wcf" . WCF_N . "_package_update
			WHERE   packageUpdateServerID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$packageUpdateServerID]);
		$packageUpdateIDs = $statement->fetchMap('package', 'packageUpdateID');
		
		$sql = "INSERT INTO     wcf" . WCF_N . "_package_update_version
					(filename, license, licenseURL, isAccessible, packageDate, packageUpdateID, packageVersion)
			VALUES          (?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($allNewPackages as $package => $packageData) {
			foreach ($packageData['versions'] as $packageVersion => $versionData) {
				$statement->execute([
					$versionData['file'] ?? '',
					$versionData['license']['license'] ?? '',
					$versionData['license']['licenseURL'] ?? '',
					$versionData['isAccessible'] ? 1 : 0,
					$versionData['packageDate'],
					$packageUpdateIDs[$package],
					$packageVersion,
				]);
			}
		}
		WCF::getDB()->commitTransaction();
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add('packageUpdateID IN (?)', [array_values($packageUpdateIDs)]);
		$sql = "SELECT  packageUpdateVersionID, packageUpdateID, packageVersion
			FROM    wcf" . WCF_N . "_package_update_version
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageUpdateVersions = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($packageUpdateVersions[$row['packageUpdateID']])) {
				$packageUpdateVersions[$row['packageUpdateID']] = [];
			}
			
			$packageUpdateVersions[$row['packageUpdateID']][$row['packageVersion']] = $row['packageUpdateVersionID'];
		}
		
		foreach ($allNewPackages as $package => $packageData) {
			foreach ($packageData['versions'] as $packageVersion => $versionData) {
				$packageUpdateID = $packageUpdateIDs[$package];
				$packageUpdateVersionID = $packageUpdateVersions[$packageUpdateID][$packageVersion];
				
				if (isset($versionData['requiredPackages'])) {
					foreach ($versionData['requiredPackages'] as $requiredIdentifier => $required) {
						$requirementInserts[] = [
							'packageUpdateVersionID' => $packageUpdateVersionID,
							'package' => $requiredIdentifier,
							'minversion' => $required['minversion'] ?? ''
						];
					}
				}
				
				if (isset($versionData['optionalPackages'])) {
					foreach ($versionData['optionalPackages'] as $optionalPackage) {
						$optionalInserts[] = [
							'packageUpdateVersionID' => $packageUpdateVersionID,
							'package' => $optionalPackage
						];
					}
				}
				
				if (isset($versionData['excludedPackages'])) {
					foreach ($versionData['excludedPackages'] as $excludedIdentifier => $exclusion) {
						$excludedPackagesParameters[] = [
							'packageUpdateVersionID' => $packageUpdateVersionID,
							'excludedPackage' => $excludedIdentifier,
							'excludedPackageVersion' => $exclusion['version'] ?? ''
						];
					}
				}
				
				if (isset($versionData['fromversions'])) {
					foreach ($versionData['fromversions'] as $fromversion) {
						$fromversionInserts[] = [
							'packageUpdateVersionID' => $packageUpdateVersionID,
							'fromversion' => $fromversion
						];
					}
				}
				
				// @deprecated 5.2
				if (isset($versionData['compatibility'])) {
					foreach ($versionData['compatibility'] as $version) {
						$compatibilityInserts[] = [
							'packageUpdateVersionID' => $packageUpdateVersionID,
							'version' => $version
						];
					}
					
					// The API compatibility versions are deprecated, any package that exposes them must
					// exclude at most `com.woltlab.wcf` in version `6.0.0 Alpha 1`.
					if (!empty($compatibilityInserts)) {
						if (!isset($versionData['excludedPackages'])) $versionData['excludedPackages'] = [];
						$excludeCore60 = '6.0.0 Alpha 1';
						
						$coreExclude = null;
						$versionData['excludedPackages'] = array_filter($versionData['excludedPackages'], function($excludedPackage, $excludedVersion) use (&$coreExclude) {
							if ($excludedPackage === 'com.woltlab.wcf') {
								$coreExclude = $excludedVersion;
								return false;
							}
							
							return true;
						}, ARRAY_FILTER_USE_BOTH);
						
						if ($coreExclude === null || Package::compareVersion($coreExclude, $excludeCore60, '>')) {
							$versionData['excludedPackages'][] = [
								'packageUpdateVersionID' => $packageUpdateVersionID,
								'excludedPackage' => 'com.woltlab.wcf',
								'excludedPackageVersion' => $excludeCore60,
							];
						}
					}
				}
			}
		}
		
		if (!empty($requirementInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_requirement
						(packageUpdateVersionID, package, minversion)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($requirementInserts as $requirement) {
				$statement->execute([
					$requirement['packageUpdateVersionID'],
					$requirement['package'],
					$requirement['minversion']
				]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($optionalInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_optional
						(packageUpdateVersionID, package)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($optionalInserts as $requirement) {
				$statement->execute([
					$requirement['packageUpdateVersionID'],
					$requirement['package']
				]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($excludedPackagesParameters)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_exclusion
						(packageUpdateVersionID, excludedPackage, excludedPackageVersion)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($excludedPackagesParameters as $excludedPackage) {
				$statement->execute([
					$excludedPackage['packageUpdateVersionID'],
					$excludedPackage['excludedPackage'],
					$excludedPackage['excludedPackageVersion']
				]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($fromversionInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_update_fromversion
						(packageUpdateVersionID, fromversion)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($fromversionInserts as $fromversion) {
				$statement->execute([
					$fromversion['packageUpdateVersionID'],
					$fromversion['fromversion']
				]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		// @deprecated 5.2
		if (!empty($compatibilityInserts)) {
			$sql = "INSERT INTO     wcf".WCF_N."_package_update_compatibility
						(packageUpdateVersionID, version)
				VALUES          (?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($compatibilityInserts as $versionData) {
				$statement->execute([
					$versionData['packageUpdateVersionID'],
					$versionData['version']
				]);
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
	 * @throws      SystemException
	 */
	public function getAvailableUpdates($removeRequirements = true, $removeOlderMinorReleases = false) {
		$updates = [];
		
		// get update server data
		$updateServers = PackageUpdateServer::getActiveUpdateServers();
		$packageUpdateServerIDs = array_keys($updateServers);
		if (empty($packageUpdateServerIDs)) return $updates;
		
		// get existing packages and their versions
		$existingPackages = [];
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
		$conditions->add("pu.packageUpdateServerID IN (?)", [$packageUpdateServerIDs]);
		$conditions->add("package IN (SELECT DISTINCT package FROM wcf".WCF_N."_package)");
		
		$sql = "SELECT		pu.packageUpdateID, pu.packageUpdateServerID, pu.package,
					puv.packageUpdateVersionID, puv.packageDate, puv.filename, puv.packageVersion
			FROM		wcf".WCF_N."_package_update pu
			LEFT JOIN	wcf".WCF_N."_package_update_version puv
			ON		(puv.packageUpdateID = pu.packageUpdateID AND puv.isAccessible = 1)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($existingPackages[$row['package']])) {
				if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
					throw new SystemException("Invalid package update data, identifier '" . $row['package'] . "' does not match any installed package (case-mismatch).");
				}
				
				// case-mismatch, skip the update
				continue;
			}
			
			// test version
			foreach ($existingPackages[$row['package']] as $existingVersion) {
				if (Package::compareVersion($existingVersion['packageVersion'], $row['packageVersion'], '<')) {
					// package data
					if (!isset($updates[$existingVersion['packageID']])) {
						$existingVersion['versions'] = [];
						$updates[$existingVersion['packageID']] = $existingVersion;
					}
					
					// version data
					if (!isset($updates[$existingVersion['packageID']]['versions'][$row['packageVersion']])) {
						$updates[$existingVersion['packageID']]['versions'][$row['packageVersion']] = [
							'packageDate' => $row['packageDate'],
							'packageVersion' => $row['packageVersion'],
							'servers' => []
						];
					}
					
					// server data
					$updates[$existingVersion['packageID']]['versions'][$row['packageVersion']]['servers'][] = [
						'packageUpdateID' => $row['packageUpdateID'],
						'packageUpdateServerID' => $row['packageUpdateServerID'],
						'packageUpdateVersionID' => $row['packageUpdateVersionID'],
						'filename' => $row['filename']
					];
				}
			}
		}
		
		// sort package versions
		// and remove old versions
		foreach ($updates as $packageID => $data) {
			uksort($updates[$packageID]['versions'], ['wcf\data\package\Package', 'compareVersion']);
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
				$highestVersions = [];
				foreach ($updateData['versions'] as $versionNumber => $dummy) {
					if (preg_match('~^(\d+\.\d+)\.~', $versionNumber, $matches)) {
						$major = $matches[1];
						if (isset($highestVersions[$major])) {
							if (Package::compareVersion($highestVersions[$major], $versionNumber, '<')) {
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
		$statement->execute([$packageUpdateVersionID]);
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
	 * @param	array	$selectedPackages
	 * @return	PackageInstallationScheduler
	 */
	public function prepareInstallation(array $selectedPackages) {
		return new PackageInstallationScheduler($selectedPackages);
	}
	
	/**
	 * Returns package update versions of the specified package.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @return	array		package update versions
	 * @throws	SystemException
	 */
	public function getPackageUpdateVersions($package, $version = '') {
		// get newest package version
		if (empty($version)) {
			$version = $this->getNewestPackageVersion($package);
		}
		
		// get versions
		$sql = "SELECT		puv.*, pu.*, pus.serverURL, pus.loginUsername, pus.loginPassword
			FROM		wcf".WCF_N."_package_update_version puv
			LEFT JOIN	wcf".WCF_N."_package_update pu
			ON		(pu.packageUpdateID = puv.packageUpdateID)
			LEFT JOIN	wcf".WCF_N."_package_update_server pus
			ON		(pus.packageUpdateServerID = pu.packageUpdateServerID)
			WHERE		pu.package = ?
					AND puv.packageVersion = ?
					AND puv.isAccessible = ?
					AND pus.isDisabled = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$package,
			$version,
			1,
			0
		]);
		$versions = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		if (empty($versions)) {
			throw new SystemException("Cannot find package '".$package."' in version '".$version."'");
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
		$versions = [];
		$sql = "SELECT	packageVersion
			FROM	wcf".WCF_N."_package_update_version
			WHERE	packageUpdateID IN (
					SELECT	packageUpdateID
					FROM	wcf".WCF_N."_package_update
					WHERE	package = ?
				)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$package]);
		while ($row = $statement->fetchArray()) {
			$versions[$row['packageVersion']] = $row['packageVersion'];
		}
		
		// sort by version number
		usort($versions, [Package::class, 'compareVersion']);
		
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
			$cachedDownloads = [];
		}
		
		// store in session
		$cachedDownloads[$package.'@'.$version] = $filename;
		WCF::getSession()->register('cachedPackageUpdateDownloads', $cachedDownloads);
	}
}
