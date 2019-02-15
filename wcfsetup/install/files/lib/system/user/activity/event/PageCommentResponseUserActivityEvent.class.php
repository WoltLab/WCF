<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\page\PageCache;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for responses to page comments.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 * @since	5.2
 */
class PageCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
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
			$userIDs[] = $comment->userID;
		}
		
		if (!empty($userIDs)) {
			$userList = new UserList();
			$userList->setObjectIDs($userIDs);
			$userList->readObjects();
			$users = $userList->getObjects();
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($responses[$event->objectID])) {
				$response = $responses[$event->objectID];
				$comment = $comments[$response->commentID];
				if (PageCache::getInstance()->getPage($comment->objectID) && isset($users[$comment->userID])) {
					$page = PageCache::getInstance()->getPage($comment->objectID);
					
					// check permissions
					if (!$page->isAccessible()) {
						continue;
					}
					$event->setIsAccessible();
					
					// title
					$text = WCF::getLanguage()->getDynamicVariable('wcf.page.recentActivity.pageCommentResponse', [
						'commentAuthor' => $users[$comment->userID],
						'commentID' => $comment->commentID,
						'responseID' => $response->responseID,
						'page' => $page
					]);
					$event->setTitle($text);
					
					// description
					$event->setDescription($response->getExcerpt());
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
