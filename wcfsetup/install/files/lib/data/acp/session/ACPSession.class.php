<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an ACP session.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session
 * @category 	Community Framework
 */
class ACPSession extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_session';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexIsIdentity
	 */
	protected static $databaseTableIndexIsIdentity = false;
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'sessionID';
}
?>
