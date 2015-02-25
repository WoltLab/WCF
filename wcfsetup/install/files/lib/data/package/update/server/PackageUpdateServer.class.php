<?php
namespace wcf\data\package\update\server;
use wcf\data\DatabaseObject;
use wcf\system\io\RemoteFile;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents a package update server.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.server
 * @category	Community Framework
 */
class PackageUpdateServer extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package_update_server';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageUpdateServerID';
	
	/**
	 * API meta data
	 * @var	array
	 */
	protected $metaData = array();
	
	/**
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		if (!empty($data['metaData'])) {
			$metaData = @unserialize($data['metaData']);
			if (is_array($metaData)) {
				$this->metaData = $metaData;
			}
			
			unset($data['metaData']);
		}
		
		parent::handleData($data);
	}
	
	/**
	 * Returns all active update package servers sorted by hostname.
	 * 
	 * @param	array		$packageUpdateServerIDs
	 * @return	array		$servers
	 */
	public static function getActiveUpdateServers(array $packageUpdateServerIDs = array()) {
		$list = new PackageUpdateServerList();
		$list->getConditionBuilder()->add("isDisabled = ?", array(0));
		if (!empty($packageUpdateServerIDs)) {
			$list->getConditionBuilder()->add("packageUpdateServerID IN (?)", array($packageUpdateServerIDs));
		}
		$list->readObjects();
		
		return $list->getObjects();
	}
	
	/**
	 * Validates a server url.
	 * 
	 * @param	string		$serverURL
	 * @return	boolean		validates
	 */
	public static function isValidServerURL($serverURL) {
		if (trim($serverURL)) {
			if (!$parsedURL = @parse_url($serverURL))
				return false;
			if (!isset($parsedURL['scheme']) || ($parsedURL['scheme'] != 'http' && $parsedURL['scheme'] != 'https'))
				return false;
			if (!isset($parsedURL['host']))
				return false;
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Gets stored auth data of this update server.
	 * 
	 * @return	array		$authData
	 */
	public function getAuthData() {
		$authData = array();
		// database data
		if ($this->loginUsername != '' && $this->loginPassword != '') {
			$authData = array(
				'username' => $this->loginUsername,
				'password' => $this->loginPassword
			);
		}
		
		// session data
		$packageUpdateAuthData = @unserialize(WCF::getSession()->getVar('packageUpdateAuthData'));
		if ($packageUpdateAuthData !== null && isset($packageUpdateAuthData[$this->packageUpdateServerID])) {
			$authData = $packageUpdateAuthData[$this->packageUpdateServerID];
		}
		
		return $authData;
	}
	
	/**
	 * Stores auth data for a package update server.
	 * 
	 * @param	integer		$packageUpdateServerID
	 * @param	string		$username
	 * @param	string		$password
	 * @param	boolean		$saveCredentials
	 */
	public static function storeAuthData($packageUpdateServerID, $username, $password, $saveCredentials = false) {
		$packageUpdateAuthData = @unserialize(WCF::getSession()->getVar('packageUpdateAuthData'));
		if ($packageUpdateAuthData === null || !is_array($packageUpdateAuthData)) {
			$packageUpdateAuthData = array();
		}
		
		$packageUpdateAuthData[$packageUpdateServerID] = array(
			'username' => $username,
			'password' => $password
		);
		
		WCF::getSession()->register('packageUpdateAuthData', serialize($packageUpdateAuthData));
		
		if ($saveCredentials) {
			$serverAction = new PackageUpdateServerAction(array($packageUpdateServerID), 'update', array('data' => array(
				'loginUsername' => $username,
				'loginPassword' => $password
			)));
			$serverAction->executeAction();
		}
	}
	
	/**
	 * Returns true if update server requires license data instead of username/password.
	 * 
	 * @return	integer
	 */
	public final function requiresLicense() {
		return Regex::compile('^https?://update.woltlab.com/')->match($this->serverURL);
	}
	
	/**
	 * Returns the highlighed server URL.
	 * 
	 * @return	string
	 */
	public function getHighlightedURL() {
		$host = parse_url($this->serverURL, PHP_URL_HOST);
		return str_replace($host, '<strong>'.$host.'</strong>', $this->serverURL);
	}
	
	/**
	 * Returns the list endpoint for package servers.
	 * 
	 * @param	boolean		$forceHTTP
	 * @return	string
	 */
	public function getListURL($forceHTTP = false) {
		if ($this->apiVersion == '2.0') {
			return $this->serverURL;
		}
		
		$serverURL = FileUtil::addTrailingSlash($this->serverURL) . 'list/' . WCF::getLanguage()->getFixedLanguageCode() . '.xml';
		
		$metaData = $this->getMetaData();
		if ($forceHTTP || !RemoteFile::supportsSSL() || !$metaData['ssl']) {
			return preg_replace('~^https://~', 'http://', $serverURL);
		}
		
		return preg_replace('~^http://~', 'https://', $serverURL);
	}
	
	/**
	 * Returns the download endpoint for package servers.
	 * 
	 * @return	string
	 */
	public function getDownloadURL() {
		if ($this->apiVersion == '2.0') {
			return $this->serverURL;
		}
		
		$metaData = $this->getMetaData();
		if (!RemoteFile::supportsSSL() || !$metaData['ssl']) {
			return preg_replace('~^https://~', 'http://', $this->serverURL);
		}
		
		return preg_replace('~^http://~', 'https://', $this->serverURL);
	}
	
	/**
	 * Returns API meta data.
	 * 
	 * @return	array
	 */
	public function getMetaData() {
		return $this->metaData;
	}
	
	/**
	 * Returns true if a request to this server would make use of a secure connection.
	 * 
	 * @return	boolean
	 */
	public function attemptSecureConnection() {
		if ($this->apiVersion == '2.0') {
			return false;
		}
		
		$metaData = $this->getMetaData();
		if (RemoteFile::supportsSSL() && $metaData['ssl']) {
			return true;
		}
		
		return false;
	}
}
