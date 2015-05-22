<?php
namespace wcf\data\event\listener;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;

/**
 * Represents an event listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.event.listener
 * @category	Community Framework
 */
class EventListener extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'event_listener';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'listenerID';
}
