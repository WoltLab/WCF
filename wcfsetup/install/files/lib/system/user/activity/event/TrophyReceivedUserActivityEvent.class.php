<?php
namespace wcf\system\user\activity\event;
use wcf\data\user\trophy\UserTrophyList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User activity event implementation for receiving a trophy.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 */
class TrophyReceivedUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		if (!MODULE_TROPHY || !WCF::getSession()->getPermission('user.profile.trophy.canSeeTrophies')) return;
		
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		$trophyList = new UserTrophyList();
		$trophyList->getConditionBuilder()->add("user_trophy.userTrophyID IN (?)", [$objectIDs]);
		$trophyList->readObjects();
		$trophies = $trophyList->getObjects();
		
		foreach ($events as $event) {
			if (isset($trophies[$event->objectID])) {
				if (!$trophies[$event->objectID]->canSee()) {
					continue;
				}
				
				$event->setIsAccessible();
				
				$event->setTitle(WCF::getLanguage()->getDynamicVariable('wcf.user.trophy.recentActivity.received', ['userTrophy' => $trophies[$event->objectID]]));
				$event->setDescription(StringUtil::encodeHTML($trophies[$event->objectID]->getDescription()));
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
