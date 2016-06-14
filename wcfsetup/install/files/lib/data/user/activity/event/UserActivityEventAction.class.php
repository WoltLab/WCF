<?php
namespace wcf\data\user\activity\event;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Executes user activity event-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Activity\Event
 * 
 * @method	UserActivityEvent		create()
 * @method	UserActivityEventEditor[]	getObjects()
 * @method	UserActivityEventEditor		getSingleObject()
 */
class UserActivityEventAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	public $allowGuestAccess = ['load'];
	
	/**
	 * Validates parameters to load recent activity entries.
	 */
	public function validateLoad() {
		$this->readBoolean('filteredByFollowedUsers', true);
		$this->readInteger('lastEventTime');
		$this->readInteger('lastEventID', true);
		$this->readInteger('userID', true);
	}
	
	/**
	 * Loads a list of recent activity entries.
	 * 
	 * @return	array
	 */
	public function load() {
		$eventList = new ViewableUserActivityEventList();
		if ($this->parameters['lastEventID']) {
			$eventList->getConditionBuilder()->add("user_activity_event.time <= ?", [$this->parameters['lastEventTime']]);
			$eventList->getConditionBuilder()->add("user_activity_event.eventID < ?", [$this->parameters['lastEventID']]);
		}
		else {
			$eventList->getConditionBuilder()->add("user_activity_event.time < ?", [$this->parameters['lastEventTime']]);
		}
		
		// profile view
		if ($this->parameters['userID']) {
			$eventList->getConditionBuilder()->add("user_activity_event.userID = ?", [$this->parameters['userID']]);
		}
		else {
			/** @noinspection PhpUndefinedMethodInspection */
			if ($this->parameters['filteredByFollowedUsers'] && count(WCF::getUserProfileHandler()->getFollowingUsers())) {
				/** @noinspection PhpUndefinedMethodInspection */
				$eventList->getConditionBuilder()->add('user_activity_event.userID IN (?)', [WCF::getUserProfileHandler()->getFollowingUsers()]);
			}
		}
		
		$eventList->readObjects();
		$lastEventTime = $eventList->getLastEventTime();
		
		if (!$lastEventTime) {
			return [];
		}
		
		// removes orphaned and non-accessable events
		UserActivityEventHandler::validateEvents($eventList);
		
		if (!count($eventList)) {
			return [];
		}
		
		// parse template
		WCF::getTPL()->assign([
			'eventList' => $eventList
		]);
		
		$events = $eventList->getObjects();
		return [
			'lastEventID' => end($events)->eventID,
			'lastEventTime' => $lastEventTime,
			'template' => WCF::getTPL()->fetch('recentActivityListItem')
		];
	}
	
	/**
	 * Does nothing.
	 */
	public function validateSwitchContext() { }
	
	public function switchContext() {
		/** @noinspection PhpUndefinedFieldInspection */
		$userAction = new UserAction([WCF::getUser()], 'update', [
			'options' => [
				User::getUserOptionID('recentActivitiesFilterByFollowing') => (WCF::getUser()->recentActivitiesFilterByFollowing ? 0 : 1)
			]
		]);
		$userAction->executeAction();
	}
}
