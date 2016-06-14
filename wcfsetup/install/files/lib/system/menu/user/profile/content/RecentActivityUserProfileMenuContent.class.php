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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User\Profile\Content
 */
class RecentActivityUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * @inheritDoc
	 */
	public function getContent($userID) {
		$eventList = new ViewableUserActivityEventList();
		$eventList->getConditionBuilder()->add("user_activity_event.userID = ?", [$userID]);
		$eventList->readObjects();
		
		$lastEventTime = $eventList->getLastEventTime();
		if ($lastEventTime) {
			UserActivityEventHandler::validateEvents($eventList);
		}
		
		WCF::getTPL()->assign([
			'eventList' => $eventList,
			'lastEventTime' => $lastEventTime,
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
