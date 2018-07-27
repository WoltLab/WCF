<?php
namespace wcf\data\event\listener;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit event listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Event\Listener
 * 
 * @method static	EventListener	create(array $parameters = [])
 * @method		EventListener	getDecoratedObject()
 * @mixin		EventListener
 */
class EventListenerEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = EventListener::class;
}
