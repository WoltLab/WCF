<?php
namespace wcf\system\box;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Box for recent activities.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class RecentActivityBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['contentTop', 'contentBottom', 'sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.user.recentActivity');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('RecentActivityList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if ($this->getBox()->position == 'contentTop' || $this->getBox()->position == 'contentBottom') {
			$canFilterByFollowedUsers = $filteredByFollowedUsers = false;
			if (WCF::getUser()->userID && count(WCF::getUserProfileHandler()->getFollowingUsers())) {
				$canFilterByFollowedUsers = true;
			}
			
			$eventList = new ViewableUserActivityEventList();
			if ($canFilterByFollowedUsers && WCF::getUser()->recentActivitiesFilterByFollowing) {
				$filteredByFollowedUsers = true;
				$eventList->getConditionBuilder()->add('user_activity_event.userID IN (?)', [WCF::getUserProfileHandler()->getFollowingUsers()]);
			}
			$eventList->sqlLimit = RECENT_ACTIVITY_ITEMS;
			$eventList->readObjects();
			$lastEventTime = $eventList->getLastEventTime();
			
			// removes orphaned and non-accessable events
			UserActivityEventHandler::validateEvents($eventList);
			
			if (count($eventList) || $filteredByFollowedUsers) {
				WCF::getTPL()->assign([
					'canFilterByFollowedUsers' => $canFilterByFollowedUsers,
					'eventList' => $eventList, 'lastEventTime' => $lastEventTime,
					'filteredByFollowedUsers' => $filteredByFollowedUsers
				]);
				
				$this->content = WCF::getTPL()->fetch('boxRecentActivity');
			}
		}
		else {
			$eventList = new ViewableUserActivityEventList();
			$eventList->sqlLimit = RECENT_ACTIVITY_SIDEBAR_ITEMS;
			$eventList->readObjects();
			
			// removes orphaned and non-accessable events
			UserActivityEventHandler::validateEvents($eventList);
			
			if (count($eventList)) {
				WCF::getTPL()->assign([
					'eventList' => $eventList
				]);
				
				$this->content = WCF::getTPL()->fetch('boxRecentActivitySidebar');
			}
		}
	}
}
