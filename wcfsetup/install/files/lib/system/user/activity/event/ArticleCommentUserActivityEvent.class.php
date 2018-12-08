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
 * @copyright	2001-2018 WoltLab GmbH
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
		$articleContentIDs = [];
		foreach ($comments as $comment) {
			$articleContentIDs[] = $comment->objectID;
		}
		
		$articles = $articleContentToArticle = [];
		if (!empty($articleContentIDs)) {
			$articleList = new ViewableArticleList();
			$articleList->getConditionBuilder()->add("article.articleID IN (SELECT articleID FROM wcf".WCF_N."_article_content WHERE articleContentID IN (?))", [$articleContentIDs]);
			$articleList->readObjects();
			foreach ($articleList as $article) {
				$articles[$article->articleID] = $article;
				
				$articleContentToArticle[$article->getArticleContent()->articleContentID] = $article->articleID;
			}
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($comments[$event->objectID])) {
				// short output
				$comment = $comments[$event->objectID];
				if (isset($articleContentToArticle[$comment->objectID])) {
					$article = $articles[$articleContentToArticle[$comment->objectID]];
					
					// check permissions
					if (!$article->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// add title
					$text = WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.articleComment', [
						'article' => $article,
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
