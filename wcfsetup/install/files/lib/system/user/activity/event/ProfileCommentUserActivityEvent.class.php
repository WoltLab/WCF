<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\CommentList;
use wcf\data\user\UserProfileList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for profile comments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.activity.event
 * @category	Community Framework
 */
class ProfileCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @see	\wcf\system\user\activity\event\IUserActivityEvent::prepare()
	 */
	public function prepare(array $events) {
		if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
			return;
		}
		
		$comments = $commentIDs = array();
		foreach ($events as $event) {
			$commentIDs[] = $event->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($commentIDs));
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$userIDs = $users = array();
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
		}
		if (!empty($userIDs)) {
			$userList = new UserProfileList();
			$userList->getConditionBuilder()->add("user_table.userID IN (?)", array($userIDs));
			$userList->readObjects();
			$users = $userList->getObjects();
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
						$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.profileComment', array('user' => $user));
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
