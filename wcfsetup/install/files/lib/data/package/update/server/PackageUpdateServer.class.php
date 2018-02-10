<?php
declare(strict_types=1);
namespace wcf\data\package\update\server;
use wcf\data\DatabaseObject;
use wcf\system\io\RemoteFile;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\Url;

/**
 * Represents a package update server.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update\Server
 *
 * @property-read	integer		$packageUpdateServerID		unique id of the package update server
 * @property-read	string		$serverURL			url of the package update server
 * @property-read	string		$loginUsername			username used to login on the package update server
 * @property-read	string		$loginPassword			password used to login on the package update server
 * @property-read	integer		$isDisabled			is `1` if the package update server is disabled and thus not considered for package updates, otherwise `0`
 * @property-read	integer		$lastUpdateTime			timestamp at which the data of the package update server has been fetched the last time
 * @property-read	string		$status				status of the package update server (`online` or `offline`)
 * @property-read	string		$errorMessage			error message if the package update server if offline or empty otherwise 
 * @property-read	string		$apiVersion			version of the supported package update server api (`2.0`, `2.1`)
 */
class PackageUpdateServer extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'packageUpdateServerID';
	
	/**
	 * API meta data
	 * @var	array
	 */
	protected $metaData = [];
	
	/**
	 * @inheritDoc
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
	 * @param	integer[]	$packageUpdateServerIDs
	 * @return	PackageUpdateServer[]
	 */
	public static function getActiveUpdateServers(array $packageUpdateServerIDs = []) {
		$list = new PackageUpdateServerList();
		$list->getConditionBuilder()->add("isDisabled = ?", [0]);
		if (!empty($packageUpdateServerIDs)) {
			$list->getConditionBuilder()->add("packageUpdateServerID IN (?)", [$packageUpdateServerIDs]);
		}
		$list->readObjects();
		
		return $list->getObjects();
	}
	
	/**
	 * Returns true if the given server url is valid.
	 * 
	 * @param	string		$serverURL
	 * @return	boolean
	 */
	public static function isValidServerURL($serverURL) {
		$parsedURL = Url::parse($serverURL);
		
		return (in_array($parsedURL['scheme'], ['http', 'https']) && $parsedURL['host'] !== '');
	}
	
	/**
	 * Returns stored auth data of this update server.
	 * 
	 * @return	string[]
	 */
	public function getAuthData() {
		$authData = [];
		// database data
		if ($this->loginUsername != '' && $this->loginPassword != '') {
			$authData = [
				'username' => $this->loginUsername,
				'password' => $this->loginPassword
			];
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
			$packageUpdateAuthData = [];
		}
		
		$packageUpdateAuthData[$packageUpdateServerID] = [
			'username' => $username,
			'password' => $password
		];
		
		WCF::getSession()->register('packageUpdateAuthData', serialize($packageUpdateAuthData));
		
		if ($saveCredentials) {
			$serverAction = new PackageUpdateServerAction([$packageUpdateServerID], 'update', ['data' => [
				'loginUsername' => $username,
				'loginPassword' => $password
			]]);
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
	 * Returns the highlighted server URL.
	 * 
	 * @return	string
	 */
	public function getHighlightedURL() {
		$host = Url::parse($this->serverURL)['host'];
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
	
	/**
	 * Returns true if the host is `update.woltlab.com`.
	 * 
	 * @return      boolean
	 */
	public function isWoltLabUpdateServer() {
		return Url::parse($this->serverURL)['host'] === 'update.woltlab.com';
	}
	
	/**
	 * Returns true if the host is `store.woltlab.com`.
	 * 
	 * @return      boolean
	 */
	public function isWoltLabStoreServer() {
		return Url::parse($this->serverURL)['host'] === 'store.woltlab.com';
	}
}
