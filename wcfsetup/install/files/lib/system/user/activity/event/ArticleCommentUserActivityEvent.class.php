<?php
namespace wcf\system\user\activity\event;
use wcf\data\article\ViewableArticleList;
use wcf\data\comment\CommentList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for article comments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 * @since	3.0
 */
class ArticleCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
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
		
		// fetch articles
		$articleIDs = $articles = [];
		foreach ($comments as $comment) {
			$articleIDs[] = $comment->objectID;
		}
		if (!empty($articleIDs)) {
			$articleList = new ViewableArticleList();
			$articleList->setObjectIDs($articleIDs);
			$articleList->readObjects();
			$articles = $articleList->getObjects();
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($comments[$event->objectID])) {
				// short output
				$comment = $comments[$event->objectID];
				if (isset($articles[$comment->objectID])) {
					$article = $articles[$comment->objectID];
					
					// check permissions
					if (!$article->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// add title
					$text = WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.articleComment', ['article' => $article]);
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
