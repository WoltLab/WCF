<?php
namespace wcf\data\acp\session\log;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Represents a session log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.log
 * @category	Community Framework
 */
class ACPSessionLog extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_session_log';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'sessionLogID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::__construct()
	 */
	public function __construct($id, array $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT		acp_session_log.*, user_table.username, acp_session.sessionID AS active
				FROM		wcf".WCF_N."_acp_session_log acp_session_log
				LEFT JOIN	wcf".WCF_N."_acp_session acp_session
				ON		(acp_session.sessionID = acp_session_log.sessionID)
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = acp_session_log.userID)
				WHERE		acp_session_log.sessionLogID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($id));
			$row = $statement->fetchArray();
		}
		else if ($object !== null) {
			$row = $object->data;
		}
		
		$this->handleData($row);
	}
	
	/**
	 * Returns true if this session is active.
	 * 
	 * @return	boolean
	 */
	public function isActive() {
		if ($this->active && $this->lastActivityTime > TIME_NOW - SESSION_TIMEOUT) {
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns true if this session is the active user session.
	 * 
	 * @return	boolean
	 */
	public function isActiveUserSession() {
		if ($this->isActive() && $this->sessionID == WCF::getSession()->sessionID) {
			return 1;
		}
		
		return 0;
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
