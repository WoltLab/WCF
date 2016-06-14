<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an ACP session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session
 *
 * @property-read	string		$sessionID
 * @property-read	integer|null	$userID
 * @property-read	string		$ipAddress
 * @property-read	string		$userAgent
 * @property-read	integer		$lastActivityTime
 * @property-read	string		$requestURI
 * @property-read	string		$requestMethod
 * @property-read	string		$controller
 * @property-read	string		$parentObjectType
 * @property-read	integer		$parentObjectID
 * @property-read	string		$objectType
 * @property-read	integer		$objectID
 * @property-read	string		$sessionVariables
 */
class ACPSession extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acp_session';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexIsIdentity = false;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'sessionID';
	
	/**
	 * Returns true if this session type supports persistent logins.
	 * 
	 * @return	boolean
	 */
	public static function supportsPersistentLogins() {
		return false;
	}
	
	/**
	 * Returns true if this session type supports virtual sessions (sharing the same
	 * session among multiple clients).
	 * 
	 * @return	boolean
	 */
	public static function supportsVirtualSessions() {
		return true;
	}
	
	/**
	 * Returns the existing session object for given user id or null if there
	 * is no such session.
	 * 
	 * @param	integer		$userID
	 * @return	ACPSession
	 */
	public static function getSessionByUserID($userID) {
		$sql = "SELECT	*
			FROM	".static::getDatabaseTableName()."
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$userID]);
		
		return $statement->fetchObject(static::class);
	}
}
