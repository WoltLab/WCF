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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user.profile.content
 * @category	Community Framework
 */
class RecentActivityUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * @see	\wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
	 */
	public function getContent($userID) {
		$eventList = new ViewableUserActivityEventList();
		$eventList->getConditionBuilder()->add("user_activity_event.userID = ?", array($userID));
		$eventList->readObjects();
		
		$lastEventTime = $eventList->getLastEventTime();
		if ($lastEventTime) {
			UserActivityEventHandler::validateEvents($eventList);
		}
		
		WCF::getTPL()->assign(array(
			'eventList' => $eventList,
			'lastEventTime' => $lastEventTime,
			'placeholder' => WCF::getLanguage()->get('wcf.user.profile.content.recentActivity.noEntries'),
			'userID' => $userID
		));
		
		return WCF::getTPL()->fetch('recentActivities');
	}
	
	/**
	 * @see	\wcf\system\menu\user\profile\content\IUserProfileMenuContent::isVisible()
	 */
	public function isVisible($userID) {
		return true;
	}
}
