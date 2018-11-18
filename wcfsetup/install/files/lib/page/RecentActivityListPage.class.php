<?php
namespace wcf\page;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Shows the global recent activity list page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class RecentActivityListPage extends AbstractPage {
	/**
	 * viewable user activity event list
	 * @var	ViewableUserActivityEventList
	 */
	public $eventList = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('RecentActivityList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->eventList = new ViewableUserActivityEventList();
		
		if (!empty(UserProfileHandler::getInstance()->getIgnoredUsers())) {
			$this->eventList->getConditionBuilder()->add("user_activity_event.userID NOT IN (?)", [UserProfileHandler::getInstance()->getIgnoredUsers()]);
		}
		
		// load more items than necessary to avoid empty list if some items are invisible for current user
		$this->eventList->sqlLimit = 60;
		
		$this->eventList->readObjects();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// removes orphaned and non-accessible events
		UserActivityEventHandler::validateEvents($this->eventList);
		
		// remove unused items
		$this->eventList->truncate(20);
		
		WCF::getTPL()->assign([
			'eventList' => $this->eventList,
			'lastEventTime' => $this->eventList->getLastEventTime()
		]);
	}
}
