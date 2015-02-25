<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObject;
use wcf\util\UserUtil;

/**
 * Represents a session access log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category	Community Framework
 */
class ACPSessionAccessLog extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_session_access_log';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
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
