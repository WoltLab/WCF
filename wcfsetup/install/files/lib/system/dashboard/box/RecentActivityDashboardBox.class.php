<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\page\IPage;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Dashboard box for recent activity.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class RecentActivityDashboardBox extends AbstractContentDashboardBox {
	/**
	 * true, if user can filter by followed users
	 * @var	boolean
	 */
	public $canFilterByFollowedUsers = false;
	
	/**
	 * recent activity list
	 * @var	\wcf\data\user\activity\event\ViewableUserActivityEventList
	 */
	public $eventList = null;
	
	/**
	 * true, if results were filtered by followed users
	 * @var	boolean
	 */
	public $filteredByFollowedUsers = false;
	
	/**
	 * latest event time
	 * @var	integer
	 */
	public $lastEventTime = 0;
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		if (WCF::getUser()->userID && count(WCF::getUserProfileHandler()->getFollowingUsers())) {
			$this->canFilterByFollowedUsers = true;
		}
		
		$this->eventList = new ViewableUserActivityEventList();
		if ($this->canFilterByFollowedUsers && WCF::getUser()->recentActivitiesFilterByFollowing) {
			$this->filteredByFollowedUsers = true;
			$this->eventList->getConditionBuilder()->add('user_activity_event.userID IN (?)', array(WCF::getUserProfileHandler()->getFollowingUsers()));
		}
		$this->eventList->sqlLimit = RECENT_ACTIVITY_ITEMS;
		$this->eventList->readObjects();
		$this->lastEventTime = $this->eventList->getLastEventTime();
		
		// removes orphaned and non-accessable events
		UserActivityEventHandler::validateEvents($this->eventList);
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (count($this->eventList) || $this->filteredByFollowedUsers) {
			WCF::getTPL()->assign(array(
				'canFilterByFollowedUsers' => $this->canFilterByFollowedUsers,
				'eventList' => $this->eventList,
				'lastEventTime' => $this->lastEventTime,
				'filteredByFollowedUsers' => $this->filteredByFollowedUsers
			));
			
			return WCF::getTPL()->fetch('dashboardBoxRecentActivity');
		}
		
		return '';
	}
}
