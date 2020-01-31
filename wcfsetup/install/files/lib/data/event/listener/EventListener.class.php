<?php
namespace wcf\data\event\listener;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;

/**
 * Represents an event listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Event\Listener
 *
 * @property-read	integer		$listenerID		unique id of the event listener
 * @property-read	integer		$packageID		id of the package which delivers the event listener
 * @property-read	string		$environment		environment in which the event listener is executed, possible values: 'all', 'user' or 'admin'
 * @property-read	string		$listenerName		name and textual identifier of the event listener
 * @property-read	string		$eventClassName		name of the class in which the listened event is fired
 * @property-read	string		$eventName		name of the listened event
 * @property-read	string		$listenerClassName	class name of the event listener class
 * @property-read	integer		$inherit		is `1` if the event listener is also executed for classes inheriting from the listened class, otherwise `0`
 * @property-read	integer		$niceValue		value from [-128, 127] used to determine event listener execution order (event listeners with smaller `$niceValue` are executed first)
 * @property-read	string		$permissions		comma separated list of user group permissions of which the active user needs to have at least one for the event listener to be executed
 * @property-read	string		$options		comma separated list of options of which at least one needs to be enabled for the event listener to be executed
 */
class EventListener extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * prefix of automatically created event listener names
	 * @var	string
	 * @deprecated	will be removed once listener names are mandatory
	 */
	const AUTOMATIC_NAME_PREFIX = 'com.woltlab.wcf.eventListener';
	
	/**
	 * Returns the names of all events listened to.
	 * 
	 * @return	string[]
	 * @since	3.0
	 */
	public function getEventNames() {
		return explode(',', $this->eventName);
	}
}
