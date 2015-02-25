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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 */
class UserActivityEventAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	public $allowGuestAccess = array('load');
	
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
			$eventList->getConditionBuilder()->add("user_activity_event.time <= ?", array($this->parameters['lastEventTime']));
			$eventList->getConditionBuilder()->add("user_activity_event.eventID < ?", array($this->parameters['lastEventID']));
		}
		else {
			$eventList->getConditionBuilder()->add("user_activity_event.time < ?", array($this->parameters['lastEventTime']));
		}
		
		// profile view
		if ($this->parameters['userID']) {
			$eventList->getConditionBuilder()->add("user_activity_event.userID = ?", array($this->parameters['userID']));
		}
		else if ($this->parameters['filteredByFollowedUsers'] && count(WCF::getUserProfileHandler()->getFollowingUsers())) {
			$eventList->getConditionBuilder()->add('user_activity_event.userID IN (?)', array(WCF::getUserProfileHandler()->getFollowingUsers()));
		}
		
		$eventList->readObjects();
		$lastEventTime = $eventList->getLastEventTime();
		
		if (!$lastEventTime) {
			return array();
		}
		
		// removes orphaned and non-accessable events
		UserActivityEventHandler::validateEvents($eventList);
		
		if (!count($eventList)) {
			return array();
		}
		
		// parse template
		WCF::getTPL()->assign(array(
			'eventList' => $eventList
		));
		
		$events = $eventList->getObjects();
		return array(
			'lastEventID' => end($events)->eventID,
			'lastEventTime' => $lastEventTime,
			'template' => WCF::getTPL()->fetch('recentActivityListItem')
		);
	}
	
	/**
	 * Does nothing.
	 */
	public function validateSwitchContext() { }
	
	public function switchContext() {
		$userAction = new UserAction(array(WCF::getUser()), 'update', array(
			'options' => array(
				User::getUserOptionID('recentActivitiesFilterByFollowing') => (WCF::getUser()->recentActivitiesFilterByFollowing ? 0 : 1)
			)
		));
		$userAction->executeAction();
	}
}
