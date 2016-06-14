<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for profile comment responses.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 */
class ProfileCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
			return;
		}
		
		$responseIDs = [];
		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}
		
		// fetch responses
		$responseList = new CommentResponseList();
		$responseList->setObjectIDs($responseIDs);
		$responseList->readObjects();
		$responses = $responseList->getObjects();
		
		// fetch comments
		$commentIDs = $comments = [];
		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		if (!empty($commentIDs)) {
			$commentList = new CommentList();
			$commentList->setObjectIDs($commentIDs);
			$commentList->readObjects();
			$comments = $commentList->getObjects();
		}
		
		// fetch users
		$userIDs = $users = [];
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
			$userIDs[] = $comment->userID;
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
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
						$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.profileCommentResponse', [
							'commentAuthor' => $users[$comment->userID],
							'user' => $users[$comment->objectID]
						]);
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
