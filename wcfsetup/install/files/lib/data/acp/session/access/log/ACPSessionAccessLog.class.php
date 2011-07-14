<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObject;

/**
 * Represents a session access log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category 	Community Framework
 */
class ACPSessionAccessLog extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_session_access_log';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'sessionAccessLogID';
	
	/**
	 * Returns true, if the URI of this log entry is protected.
	 *
	 * @return 	boolean
	 */
	public function hasProtectedURI() {
		if ($this->requestMethod != 'GET' || !preg_match('/(\?|&)(page|form)=/', $this->requestURI)) {
			return true;
		}
		
		return false;
	}
}
?>