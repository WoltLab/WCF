<?php
namespace wcf\data\event\listener;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\EventListenerCacheBuilder;

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
class EventListenerEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = EventListener::class;
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	public static function resetCache() {
		EventListenerCacheBuilder::getInstance()->reset();
	}
}
