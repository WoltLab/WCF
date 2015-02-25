<?php
namespace wcf\data\user\activity\event;
use wcf\data\user\UserProfile;
use wcf\system\language\LanguageFactory;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Represents a list of viewable user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 */
class ViewableUserActivityEventList extends UserActivityEventList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\activity\event\UserActivityEvent';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlLimit
	 */
	public $sqlLimit = 20;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'user_activity_event.time DESC, user_activity_event.eventID DESC';
	
	/**
	 * Creates a new ViewableUserActivityEventList object.
	 */
	public function __construct() {
		parent::__construct();
		
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->getConditionBuilder()->add('(user_activity_event.languageID IN (?) OR user_activity_event.languageID IS NULL)', array(WCF::getUser()->getLanguageIDs()));
		}
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		$userIDs = array();
		$eventGroups = array();
		foreach ($this->objects as &$event) {
			$userIDs[] = $event->userID;
			$event = new ViewableUserActivityEvent($event);
			
			if (!isset($eventGroups[$event->objectTypeID])) {
				$objectType = UserActivityEventHandler::getInstance()->getObjectType($event->objectTypeID);
				$eventGroups[$event->objectTypeID] = array(
					'className' => $objectType->className,
					'objects' => array()
				);
			}
			
			$eventGroups[$event->objectTypeID]['objects'][] = $event;
		}
		unset($event);
		
		// set user profiles
		if (!empty($userIDs)) {
			$userIDs = array_unique($userIDs);
			
			$users = UserProfile::getUserProfiles($userIDs);
			foreach ($this->objects as $event) {
				$event->setUserProfile($users[$event->userID]);
			}
		}
		
		// parse events
		foreach ($eventGroups as $eventData) {
			$eventClass = call_user_func(array($eventData['className'], 'getInstance'));
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
	 * @return	array<integer>
	 */
	public function validateEvents() {
		$orphanedEventIDs = array();
		
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
