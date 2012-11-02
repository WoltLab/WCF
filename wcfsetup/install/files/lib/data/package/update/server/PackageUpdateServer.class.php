<?php
namespace wcf\data\package\update\server;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Contains business logic related to handling of package update servers.
 *
 * @author	Siegfried Schweizer
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.server
 * @category	Community Framework
 */
class PackageUpdateServer extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package_update_server';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageUpdateServerID';
	
	/**
	 * Returns all active update package servers sorted by hostname.
	 * 
	 * @param	array		$packageUpdateServerIDs
	 * @return	array		$servers
	 */
	public static function getActiveUpdateServers(array $packageUpdateServerIDs = array()) {
		$list = new PackageUpdateServerList();
		$list->sqlLimit = 0;
		$list->getConditionBuilder()->add("disabled = ?", array(0));
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
			if (!isset($parsedURL['scheme']) || $parsedURL['scheme'] != 'http')
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
				'authType' => 'Basic',
				'loginUsername' => $this->loginUsername,
				'loginPassword' => $this->loginPassword
			);
		}
		
		// session data
		$packageUpdateAuthData = WCF::getSession()->getVar('packageUpdateAuthData');
		if ($packageUpdateAuthData !== null && isset($packageUpdateAuthData[$this->packageUpdateServerID])) {
			$authData = $packageUpdateAuthData[$this->packageUpdateServerID];
		}
		
		return $authData;
	}
}
