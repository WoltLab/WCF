<?php
namespace wcf\data\user\activity\event;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\language\LanguageFactory;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Represents a list of viewable user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Activity\Event
 *
 * @method	ViewableUserActivityEvent		current()
 * @method	ViewableUserActivityEvent[]		getObjects()
 * @method	ViewableUserActivityEvent|null		search($objectID)
 * @property	ViewableUserActivityEvent[]		$objects
 */
class ViewableUserActivityEventList extends UserActivityEventList {
	/**
	 * @inheritDoc
	 */
	public $className = UserActivityEvent::class;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableUserActivityEvent::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlLimit = 20;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'user_activity_event.time DESC, user_activity_event.eventID DESC';
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->getConditionBuilder()->add('(user_activity_event.languageID IN (?) OR user_activity_event.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		$userIDs = [];
		$eventGroups = [];
		foreach ($this->objects as $event) {
			$userIDs[] = $event->userID;
			
			if (!isset($eventGroups[$event->objectTypeID])) {
				$objectType = UserActivityEventHandler::getInstance()->getObjectType($event->objectTypeID);
				$eventGroups[$event->objectTypeID] = [
					'className' => $objectType->className,
					'objects' => []
				];
			}
			
			$eventGroups[$event->objectTypeID]['objects'][] = $event;
		}
		
		// set user profiles
		if (!empty($userIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs(array_unique($userIDs));
		}
		
		// parse events
		foreach ($eventGroups as $eventData) {
			$eventClass = call_user_func([$eventData['className'], 'getInstance']);
			$eventClass->prepare($eventData['objects']);
		}
	}
	
	/**
	 * Returns timestamp of oldest entry fetched.
	 * 
	 * @return	integer
	 */
	public function getLastEventTime() {
		$lastEventTime = 0;
		foreach ($this->objects as $event) {
			if (!$lastEventTime) {
				$lastEventTime = $event->time;
			}
			
			$lastEventTime = min($lastEventTime, $event->time);
		}
		
		return $lastEventTime;
	}
	
	/**
	 * Validates event permissions and returns a list of orphaned event ids.
	 * 
	 * @return	integer[]
	 */
	public function validateEvents() {
		$orphanedEventIDs = [];
		
		foreach ($this->objects as $index => $event) {
			if ($event->isOrphaned()) {
				$orphanedEventIDs[] = $event->eventID;
				unset($this->objects[$index]);
			}
			else if (!$event->isAccessible()) {
				unset($this->objects[$index]);
			}
		}
		$this->indexToObject = array_keys($this->objects);
		
		return $orphanedEventIDs;
	}
}
