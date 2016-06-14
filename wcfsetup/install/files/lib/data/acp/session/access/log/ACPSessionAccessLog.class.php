<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObject;
use wcf\util\UserUtil;

/**
 * Represents a session access log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session\Access\Log
 *
 * @property-read	integer		$sessionAccessLogID
 * @property-read	integer		$sessionLogID
 * @property-read	string		$ipAddress
 * @property-read	integer		$time
 * @property-read	string		$requestURI
 * @property-read	string		$requestMethod
 * @property-read	string		$className
 */
class ACPSessionAccessLog extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acp_session_access_log';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'sessionAccessLogID';
	
	/**
	 * Returns true if the URI of this log entry is protected.
	 * 
	 * @return	boolean
	 */
	public function hasProtectedURI() {
		if ($this->requestMethod != 'GET' || !preg_match('/(\?|&)(page|form)=/', $this->requestURI)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the ip address and attempts to convert into IPv4.
	 * 
	 * @return	string
	 */
	public function getIpAddress() {
		return UserUtil::convertIPv6To4($this->ipAddress);
	}
}
