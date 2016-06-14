<?php
namespace wcf\system\user\activity\event;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for follows.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 */
class FollowUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		// fetch user id and username
		$userList = new UserList();
		$userList->setObjectIDs($objectIDs);
		$userList->readObjects();
		$users = $userList->getObjects();
		
		// set message
		foreach ($events as $event) {
			if (isset($users[$event->objectID])) {
				$event->setIsAccessible();
				
				$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.follow', ['user' => $users[$event->objectID]]);
				$event->setTitle($text);
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
