<?php
namespace wcf\data\session;
use wcf\data\acp\session\ACPSession;

/**
 * Represents a session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Session
 * 
 * @property-read	integer|null	$pageID			id of the latest page visited
 * @property-read	integer|null	$pageObjectID		id of the object the latest page visited belongs to
 * @property-read	integer|null	$parentPageID		id of the parent page of latest page visited
 * @property-read	integer|null	$parentPageObjectID	id of the object the parent page of latest page visited belongs to
 * @property-read	integer		$spiderID		id of the spider the session belongs to
 */
class Session extends ACPSession {
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
		return SESSION_ENABLE_VIRTUALIZATION ? true : false;
	}
}
