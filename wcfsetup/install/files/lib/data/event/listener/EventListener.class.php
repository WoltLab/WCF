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
 *
 * @property-read	integer		$listenerID
 * @property-read	integer		$packageID
 * @property-read	string		$environment
 * @property-read	string		$listenerName
 * @property-read	string		$eventClassName
 * @property-read	string		$eventName
 * @property-read	string		$listenerClassName
 * @property-read	integer		$inherit
 * @property-read	integer		$niceValue
 * @property-read	string		$permissions
 * @property-read	string		$options
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
	
	/**
	 * prefix of automatically created event listener names
	 * @var	string
	 * @deprecated	will be removed once listener names are mandatory
	 */
	const AUTOMATIC_NAME_PREFIX = 'com.woltlab.wcf.eventListener';
	
	/**
	 * Returns the names of all events listened to.
	 * 
	 * @return	array<string>
	 * @since	2.2
	 */
	public function getEventNames() {
		return explode(',', $this->eventName);
	}
}
