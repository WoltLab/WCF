<?php
namespace wcf\system\menu\user\profile\content;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User\Profile\Content
 */
class RecentActivityUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * @inheritDoc
	 */
	public function getContent($userID) {
		$eventList = new ViewableUserActivityEventList();
		
		// load more items than necessary to avoid empty list if some items are invisible for current user
		$eventList->sqlLimit = 60;
		
		$eventList->getConditionBuilder()->add("user_activity_event.userID = ?", [$userID]);
		$eventList->readObjects();
		
		UserActivityEventHandler::validateEvents($eventList);
		
		// remove unused items
		$eventList->truncate(20);
		
		WCF::getTPL()->assign([
			'eventList' => $eventList,
			'lastEventTime' => $eventList->getLastEventTime(),
			'placeholder' => WCF::getLanguage()->get('wcf.user.profile.content.recentActivity.noEntries'),
			'userID' => $userID
		]);
		
		return WCF::getTPL()->fetch('recentActivities');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($userID) {
		return true;
	}
}
