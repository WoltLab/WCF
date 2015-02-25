<?php
namespace wcf\data\session\virtual;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Virtual Sessions extend the original session system with a transparent layer. 
 * It's only purpose is to enforce session validation based on IP address and/or user agent.
 * 
 * The legacy session system does not allow the same user being logged-in more than once 
 * and the same is true for WCF 2.1 unless we break most parts of the API. 
 * In order to solve this, we do allow multiple clients to share the exact same session 
 * among them, while the individual clients are tracked within wcf1_session_virtual.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 */
class SessionVirtual extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'session_virtual';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'virtualSessionID';
	
	/**
	 * Returns the active virtual session object or null.
	 * 
	 * @param	string		$sessionID
	 * @return	\wcf\data\session\virtual\SessionVirtual
	 */
	public static function getExistingSession($sessionID) {
		$sql = "SELECT	*
			FROM	".static::getDatabaseTableName()."
			WHERE	sessionID = ?
				AND ipAddress = ?
				AND userAgent = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$sessionID,
			UserUtil::getIpAddress(),
			UserUtil::getUserAgent()
		));
		
		return $statement->fetchObject(__CLASS__);
	}
	
	/**
	 * Returns the number of virtual sessions associated with the given session id.
	 * 
	 * @param	string		$sessionID
	 * @return	integer
	 */
	public static function countVirtualSessions($sessionID) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	".static::getDatabaseTableName()."
			WHERE	sessionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($sessionID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
}
