<?php
namespace wcf\data\session;
use wcf\data\acp\session\ACPSession;

/**
 * Represents a session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session
 * @category	Community Framework
 * 
 * @property-read	string		$sessionVariables
 * @property-read	string		$spiderID
 */
class Session extends ACPSession {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'session';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'sessionID';
	
	/**
	 * @see	\wcf\data\acp\session\ACPSession::supportsPersistentLogins()
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
}
