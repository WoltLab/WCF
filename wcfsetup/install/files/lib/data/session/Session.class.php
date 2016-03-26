<?php
namespace wcf\data\session;
use wcf\data\acp\session\ACPSession;
use wcf\system\WCF;

/**
 * Represents a session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session
 * @category	Community Framework
 * 
 * @property-read	string		$spiderID
 */
class Session extends ACPSession {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'session';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'sessionID';
	
	/**
	 * @inheritDoc
	 */
	public static function supportsPersistentLogins() {
		return true;
	}
	
	/**
	 * @see	\wcf\data\acp\session\ACPSession::supportsVirtualSessions()
	 */
	public static function supportsVirtualSessions() {
		return (SESSION_ENABLE_VIRTUALIZATION) ? true : false;
	}
	
	/**
	 * Returns the existing session object for given user id or null if there
	 * is no such session.
	 * 
	 * @param	integer		$userID
	 * @return	Session
	 */
	public static function getSessionByUserID($userID) {
		$sql = "SELECT	*
			FROM	".static::getDatabaseTableName()."
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$userID]);
		$row = $statement->fetchArray();
		
		if ($row === false) {
			return null;
		}
		
		return new static(null, $row);
	}
}
