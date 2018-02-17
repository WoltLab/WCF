<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an ACP session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session
 *
 * @property-read	string		$sessionID		unique textual identifier of the acp session
 * @property-read	integer|null	$userID			id of the user the acp session belongs to or `null` if the acp session belongs to a guest
 * @property-read	string		$ipAddress		id of the user whom the acp session belongs to
 * @property-read	string		$userAgent		user agent of the user whom the acp session belongs to
 * @property-read	integer		$lastActivityTime	timestamp at which the latest activity occurred
 * @property-read	string		$requestURI		uri of the latest request
 * @property-read	string		$requestMethod		used request method of the latest request (`GET`, `POST`)
 * @property-read	string		$sessionVariables	serialized array with variables stored on a session-basis
 */
class ACPSession extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexIsIdentity = false;
	
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
