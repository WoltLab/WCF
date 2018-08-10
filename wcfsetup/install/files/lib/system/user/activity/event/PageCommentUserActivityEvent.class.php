<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\CommentList;
use wcf\data\page\PageCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for page comments.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 * @since	3.2
 */
class PageCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$commentIDs = [];
		foreach ($events as $event) {
			$commentIDs[] = $event->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// set message
		foreach ($events as $event) {
			if (isset($comments[$event->objectID])) {
				// short output
				$comment = $comments[$event->objectID];
				if (PageCache::getInstance()->getPage($comment->objectID)) {
					$page = PageCache::getInstance()->getPage($comment->objectID);
					
					// check permissions
					if (!$page->isAccessible()) {
						continue;
					}
					$event->setIsAccessible();
					
					// add title
					$text = WCF::getLanguage()->getDynamicVariable('wcf.page.recentActivity.pageComment', [
						'page' => $page,
						'commentID' => $comment->commentID
					]);
					$event->setTitle($text);
					
					// add text
					$event->setDescription($comment->getExcerpt());
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
