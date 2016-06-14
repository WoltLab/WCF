<?php
namespace wcf\data\session;
use wcf\data\acp\session\ACPSession;

/**
 * Represents a session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Session
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
	 * @inheritDoc
	 */
	public static function supportsVirtualSessions() {
		return (SESSION_ENABLE_VIRTUALIZATION) ? true : false;
	}
}
