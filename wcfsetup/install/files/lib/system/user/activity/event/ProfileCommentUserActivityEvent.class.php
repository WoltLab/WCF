<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\CommentList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for profile comments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 */
class ProfileCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
			return;
		}
		
		$commentIDs = [];
		foreach ($events as $event) {
			$commentIDs[] = $event->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$userIDs = $users = [];
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($comments[$event->objectID])) {
				// short output
				$comment = $comments[$event->objectID];
				if (isset($users[$comment->objectID])) {
					if (!$users[$comment->objectID]->isProtected()) {
						$event->setIsAccessible();
						
						$user = $users[$comment->objectID];
						$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.profileComment', ['user' => $user]);
						$event->setTitle($text);
						
						// output
						$event->setDescription($comment->getExcerpt());
					}
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
