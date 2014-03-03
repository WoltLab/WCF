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
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class RecentActivityDashboardBox extends AbstractContentDashboardBox {
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
		
		$this->eventList = new ViewableUserActivityEventList();
		if (count(WCF::getUserProfileHandler()->getFollowingUsers())) {
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
		if (count($this->eventList)) {
			WCF::getTPL()->assign(array(
				'eventList' => $this->eventList,
				'lastEventTime' => $this->lastEventTime,
				'filteredByFollowedUsers' => $this->filteredByFollowedUsers
			));
			
			return WCF::getTPL()->fetch('dashboardBoxRecentActivity');
		}
	}
}
