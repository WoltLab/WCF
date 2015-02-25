<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\UserProfileList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for profile comment responses.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.activity.event
 * @category	Community Framework
 */
class ProfileCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @see	\wcf\system\user\activity\event\IUserActivityEvent::prepare()
	 */
	public function prepare(array $events) {
		if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
			return;
		}
		
		$responses = $responseIDs = array();
		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}
		
		// fetch responses
		$responseList = new CommentResponseList();
		$responseList->getConditionBuilder()->add("comment_response.responseID IN (?)", array($responseIDs));
		$responseList->readObjects();
		$responses = $responseList->getObjects();
		
		// fetch comments
		$commentIDs = $comments = array();
		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		if (!empty($commentIDs)) {
			$commentList = new CommentList();
			$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($commentIDs));
			$commentList->readObjects();
			$comments = $commentList->getObjects();
		}
		
		// fetch users
		$userIDs = $users = array();
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
			$userIDs[] = $comment->userID;
		}
		if (!empty($userIDs)) {
			$userList = new UserProfileList();
			$userList->getConditionBuilder()->add("user_table.userID IN (?)", array($userIDs));
			$userList->readObjects();
			$users = $userList->getObjects();
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($responses[$event->objectID])) {
				$response = $responses[$event->objectID];
				$comment = $comments[$response->commentID];
				if (isset($users[$comment->objectID]) && isset($users[$comment->userID])) {
					if (!$users[$comment->objectID]->isProtected()) {
						$event->setIsAccessible();
						
						// title
						$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.profileCommentResponse', array(
							'commentAuthor' => $users[$comment->userID],
							'user' => $users[$comment->objectID]
						));
						$event->setTitle($text);
						
						// description
						$event->setDescription($response->getExcerpt());
					}
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
