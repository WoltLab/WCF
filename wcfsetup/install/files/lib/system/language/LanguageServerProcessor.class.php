<?php
namespace wcf\system\language;
use wcf\data\language\Language;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\XML;

/**
 * Processes language item import from language servers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.language
 * @category	Community Framework
 */
class LanguageServerProcessor extends SingletonFactory {
	/**
	 * language object
	 * @var	\wcf\data\language\Language
	 */
	protected $language = null;
	
	/**
	 * Imports language variables for a language from given language servers.
	 * 
	 * @param	\wcf\data\language\Language			$language
	 * @param	array<\wcf\data\language\server\LanguageServer>	$languageServers
	 */
	public function import(Language $language, array $languageServers) {
		if (empty($languageServers)) return;
		$this->language = $language;
		
		// get package list
		$packageList = $this->getPackageList();
		
		foreach ($languageServers as $languageServer) {
			$this->importLanguageFile($languageServer->serverURL, $packageList);
		}
	}
	
	/**
	 * Returns list of installed packages and their associated version.
	 * 
	 * @return	array<string>
	 */
	protected function getPackageList() {
		$sql = "SELECT	package, packageVersion
			FROM	wcf".WCF_N."_package";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$packages = array();
		while ($row = $statement->fetchArray()) {
			$packages[$row['package']] = $row['packageVersion'];
		}
		
		return $packages;
	}
	
	/**
	 * Downloads and imports a language file from a language server.
	 * 
	 * @param	string		$location
	 * @param	array<string>	$packageList
	 */
	protected function importLanguageFile($location, array $packageList) {
		// get proxy
		$options = array();
		if (PROXY_SERVER_HTTP) {
			$options['http']['proxy'] = PROXY_SERVER_HTTP;
			$options['http']['request_fulluri'] = true;
		}
		
		// parse url
		$parsedURL = parse_url($location);
		$port = ($parsedURL['scheme'] == 'https' ? 443 : 80);
		$host = $parsedURL['host'];
		$path = (isset($parsedURL['path']) ? $parsedURL['path'] : '/');
		
		$remoteFile = new RemoteFile(($parsedURL['scheme'] == 'https' ? 'ssl://' : '').$host, $port, 30, $options); // the file to read.
		if (!isset($remoteFile)) {
			throw new SystemException("cannot connect to http host '".$host."'");
		}
		
		// build and send the http request
		$request = "POST ".$path." HTTP/1.0\r\n";
		$request .= "User-Agent: HTTP.PHP (LanguageServerProcessor.class.php; WoltLab Community Framework/".WCF_VERSION."; ".WCF::getLanguage()->languageCode.")\r\n";
		$request .= "Accept: */*\r\n";
		$request .= "Accept-Language: ".WCF::getLanguage()->languageCode."\r\n";
		$request .= "Host: ".$host."\r\n";
		
		// build post string
		$postString = 'languageCode='.$this->language->languageCode;
		foreach ($packageList as $package => $packageVersion) {
			$postString .= '&packages['.urlencode($package).']='.urlencode($packageVersion);
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
				$newLocation = trim($match[1]);
				if ($newLocation != $location) {
					$this->importLanguageFile($location, $packageList);
					return;
				}
			}
		}
		
		$this->parseResponse($content);
	}
	
	/**
	 * Parses XML response from language server.
	 * 
	 * @param	string		$xmlResponse
	 */
	protected function parseResponse($xmlResponse) {
		// parse xml
		$xml = new XML();
		$xml->loadXML('languageServerResponse.xml', $xmlResponse);
		$xpath = $xml->xpath();
		
		// parse variables
		$variables = array();
		$packages = $xpath->query('/ns:language/ns:package');
		foreach ($packages as $package) {
			$packageName = $package->getAttribute('name');
			$variables[$packageName] = array();
			
			$categories = $xpath->query('child::ns:category', $package);
			foreach ($categories as $category) {
				$categoryName = $category->getAttribute('name');
				$variables[$packageName][$categoryName] = array();
				
				$items = $xpath->query('child::ns:item', $category);
				foreach ($items as $item) {
					$variables[$packageName][$categoryName][$item->getAttribute('name')] = $item->nodeValue;
				}
			}
		}
		
		// try to resolve packages
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("package IN (?)", array(array_keys($variables)));
		
		$sql = "SELECT	packageID, package
			FROM	wcf".WCF_N."_package
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packages = array();
		while ($row = $statement->fetchArray()) {
			$packages[$row['package']] = $row['packageID'];
		}
		
		// ignore variables if no package is known
		if (empty($packages)) return;
		
		$this->importVariables($variables, $packages);
	}
	
	/**
	 * Imports language variables and categories.
	 * 
	 * @param	array		$variables
	 * @param	array<integer>	$packages
	 */
	protected function importVariables(array $variables, array $packages) {
		$categories = $this->importCategories($variables);
		
		$createItems = $updateItems = array();
		foreach ($packages as $package => $packageID) {
			foreach ($variables[$package] as $category => $items) {
				// get existing items
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("languageID = ?", array($this->language->languageID));
				$conditions->add("packageID = ?", array($packageID));
				$conditions->add("languageCategoryID = ?", array($categories[$category]));
				$conditions->add("languageItem IN (?)", array(array_keys($items)));
					
				$sql = "SELECT	languageItemID, languageItem
					FROM	wcf".WCF_N."_language_item
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				
				$existingItemIDs = array();
				while ($row = $statement->fetchArray()) {
					$existingItemIDs[$row['languageItem']] = $row['languageItemID'];
				}
				
				foreach ($items as $itemName => $itemValue) {
					if (isset($existingItemIDs[$itemName])) {
						$updateItems[$existingItemIDs[$itemName]] = $itemValue;
					}
					else {
						$createItems[] = array(
							'languageID' => $this->language->languageID,
							'languageItem' => $itemName,
							'languageItemValue'=> $itemValue,
							'languageItemOriginIsSystem' => 1,
							'languageCategoryID' => $categories[$category],
							'packageID' => $packageID
						);
					}
				}
			}
		}
		
		// create items
		if (!empty($createItems)) {
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				VALUES		(?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($createItems as $item) {
				$statement->execute(array(
					$item['languageID'],
					$item['languageItem'],
					$item['languageItemValue'],
					$item['languageItemOriginIsSystem'],
					$item['languageCategoryID'],
					$item['packageID']
				));
			}
		}
		
		// update items
		if (!empty($updateItems)) {
			$sql = "UPDATE	wcf".WCF_N."_language_item
				SET	languageItemValue = ?
				WHERE	languageItemID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($updateItems as $languageItemID => $languageItemValue) {
				$statement->execute(array($languageItemID, $languageItemValue));
			}
		}
	}
	
	/**
	 * Imports new langage categories.
	 * 
	 * @param	array		$variables
	 * @return	array
	 */
	protected function importCategories(array $variables) {
		// get categories
		$categoryNames = array();
		foreach ($variables as $package => $dummy) {
			$categoryNames = array_merge($categoryNames, array_keys($variables[$package]));
		}
		
		// fetch existing categories
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageCategory IN (?)", array($categoryNames));
		
		$sql = "SELECT	languageCategoryID, languageCategory
			FROM	wcf".WCF_N."_language_category
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$existingCategories = array();
		while ($row = $statement->fetchArray()) {
			$existingCategories[$row['languageCategory']] = $row['languageCategoryID'];
		}
		
		// create non-existing categories
		$createCategories = array_diff($categoryNames, array_keys($existingCategories));
		if (!empty($createCategories)) {
			// use raw queries for better performance
			$sql = "INSERT INTO	wcf".WCF_N."_language_category
						(languageCategory)
				VALUES		(?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($createCategories as $category) {
				$statement->execute(array($category));
			}
			
			// get ids for created categories
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("languageCategory IN (?)", array($createCategories));
			
			$sql = "SELECT	languageCategoryID, languageCategory
				FROM	wcf".WCF_N."_language_category
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			while ($row = $statement->fetchArray()) {
				$existingCategories[$row['languageCategory']] = $row['languageCategoryID'];
			}
		}
		
		return $existingCategories;
	}
}
