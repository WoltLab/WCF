<?php
namespace wcf\data\event\listener;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of event listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Event\Listener
 *
 * @method	EventListener		current()
 * @method	EventListener[]		getObjects()
 * @method	EventListener|null	search($objectID)
 * @property	EventListener[]		$objects
 */
class EventListenerList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = EventListener::class;
}
