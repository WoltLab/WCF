<?php
namespace wcf\system\user\activity\event;
use wcf\data\article\ViewableArticleList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for responses to article comments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 * @since	3.0
 */
class ArticleCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
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
				if (isset($articleContentToArticle[$comment->objectID]) && isset($users[$comment->userID])) {
					$article = $articles[$articleContentToArticle[$comment->objectID]];
					
					// check permissions
					if (!$article->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// title
					$text = WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.articleCommentResponse', [
						'commentAuthor' => $users[$comment->userID],
						'commentID' => $comment->commentID,
						'responseID' => $response->responseID,
						'article' => $article
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
