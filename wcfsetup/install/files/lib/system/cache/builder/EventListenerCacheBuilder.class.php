<?php
namespace wcf\system\cache\builder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Caches the event listeners.
 * 
 * Important: You cannot use \wcf\data\event\listener\EventListenerList here as
 * \wcf\data\DatabaseObjectList fires an event.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class EventListenerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$actions = array(
			'admin' => array(),
			'user' => array()
		);
		
		$inheritedActions = array(
			'admin' => array(),
			'user' => array()
		);
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_event_listener
			ORDER BY	niceValue ASC, listenerClassName ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($eventListener = $statement->fetchObject('wcf\data\event\listener\EventListener')) {
			$eventNames = $eventListener->getEventNames();
			
			if (!$eventListener->inherit) {
				if (!isset($actions[$eventListener->environment])) {
					$actions[$eventListener->environment] = array();
				}
				
				foreach ($eventNames as $eventName) {
					$key = EventHandler::generateKey($eventListener->eventClassName, $eventName);
					if (!isset($actions[$eventListener->environment][$key])) {
						$actions[$eventListener->environment][$key] = array();
					}
					
					$actions[$eventListener->environment][$key][] = $eventListener;
				}
			}
			else {
				if (!isset($inheritedActions[$eventListener->environment])) {
					$inheritedActions[$eventListener->environment] = array();
				}
				
				foreach ($eventNames as $eventName) {
					if (!isset($inheritedActions[$eventListener->environment][$eventListener->eventClassName])) {
						$inheritedActions[$eventListener->environment][$eventListener->eventClassName] = array();
					}
					
					$inheritedActions[$eventListener->environment][$eventListener->eventClassName][$eventName][] = $eventListener;
				}
			}
		}
		
		return array(
			'actions' => $actions,
			'inheritedActions' => $inheritedActions
		);
	}
}
