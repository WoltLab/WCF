<?php
namespace wcf\system\cache\builder;
use wcf\data\event\listener\EventListener;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Caches the event listeners.
 * 
 * Important: You cannot use \wcf\data\event\listener\EventListenerList here as
 * \wcf\data\DatabaseObjectList fires an event.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class EventListenerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$actions = [
			'admin' => [],
			'user' => []
		];
		
		$inheritedActions = [
			'admin' => [],
			'user' => []
		];
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_event_listener
			ORDER BY	niceValue ASC, listenerClassName ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		/** @var EventListener $eventListener */
		while ($eventListener = $statement->fetchObject(EventListener::class)) {
			$eventNames = $eventListener->getEventNames();
			
			if (!$eventListener->inherit) {
				if (!isset($actions[$eventListener->environment])) {
					$actions[$eventListener->environment] = [];
				}
				
				foreach ($eventNames as $eventName) {
					$key = EventHandler::generateKey($eventListener->eventClassName, $eventName);
					if (!isset($actions[$eventListener->environment][$key])) {
						$actions[$eventListener->environment][$key] = [];
					}
					
					$actions[$eventListener->environment][$key][] = $eventListener;
				}
			}
			else {
				if (!isset($inheritedActions[$eventListener->environment])) {
					$inheritedActions[$eventListener->environment] = [];
				}
				
				foreach ($eventNames as $eventName) {
					if (!isset($inheritedActions[$eventListener->environment][$eventListener->eventClassName])) {
						$inheritedActions[$eventListener->environment][$eventListener->eventClassName] = [];
					}
					
					$inheritedActions[$eventListener->environment][$eventListener->eventClassName][$eventName][] = $eventListener;
				}
			}
		}
		
		return [
			'actions' => $actions,
			'inheritedActions' => $inheritedActions
		];
	}
}
