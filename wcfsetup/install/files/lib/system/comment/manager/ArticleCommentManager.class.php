<?php
namespace wcf\system\comment\manager;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentList;
use wcf\data\article\ArticleEditor;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Article comment manager implementation.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment\Manager
 */
class ArticleCommentManager extends AbstractCommentManager implements IViewableLikeProvider {
	/**
	 * @inheritDoc
	 */
	protected $permissionAdd = 'user.article.canAddComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionAddWithoutModeration = 'user.article.canAddCommentWithoutModeration';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionDelete = 'user.article.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionEdit = 'user.article.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModDelete = 'mod.article.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModEdit = 'mod.article.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionCanModerate = 'mod.article.canModerateComment';
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// check object id
		$articleContent = new ArticleContent($objectID);
		if (!$articleContent->articleContentID || !$articleContent->getArticle()->canRead()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return (new ArticleContent($objectID))->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('wcf.article.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('wcf.article.comment');
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {
		$articleContent = new ArticleContent($objectID);
		$editor = new ArticleEditor($articleContent->getArticle());
		$editor->updateCounters([
			'comments' => $value
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepare(array $likes) {
		$commentLikeObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');
		
		$commentIDs = $responseIDs = [];
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				$commentIDs[] = $like->objectID;
			}
			else {
				$responseIDs[] = $like->objectID;
			}
		}
		
		// fetch response
		$userIDs = $responses = [];
		if (!empty($responseIDs)) {
			$responseList = new CommentResponseList();
			$responseList->setObjectIDs($responseIDs);
			$responseList->readObjects();
			$responses = $responseList->getObjects();
			
			foreach ($responses as $response) {
				$commentIDs[] = $response->commentID;
				if ($response->userID) {
					$userIDs[] = $response->userID;
				}
			}
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$users = [];
		$articleContentIDs = [];
		foreach ($comments as $comment) {
			$articleContentIDs[] = $comment->objectID;
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects(array_unique($userIDs));
		}
		
		// fetch articles
		$articleContents = [];
		if (!empty($articleContentIDs)) {
			$articleContentList = new ArticleContentList();
			$articleContentList->setObjectIDs($articleContentIDs);
			$articleContentList->readObjects();
			$articleContents = $articleContentList->getObjects();
		}
		
		// set message
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				// comment like
				if (isset($comments[$like->objectID])) {
					$comment = $comments[$like->objectID];
					
					if (isset($articleContents[$comment->objectID]) && $articleContents[$comment->objectID]->getArticle()->canRead()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.articleComment', [
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'comment' => $comment,
							'articleContent' => $articleContents[$comment->objectID],
							'reaction' => $like,
							// @deprecated 5.3 Use `$reaction` instead
							'like' => $like,
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($comment->getExcerpt());
					}
				}
			}
			else {
				// response like
				if (isset($responses[$like->objectID])) {
					$response = $responses[$like->objectID];
					$comment = $comments[$response->commentID];
					
					if (isset($articleContents[$comment->objectID]) && $articleContents[$comment->objectID]->getArticle()->canRead()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.articleComment.response', [
							'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'articleContent' => $articleContents[$comment->objectID],
							'reaction' => $like,
							// @deprecated 5.3 Use `$reaction` instead
							'like' => $like,
							'response' => $response,
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($response->getExcerpt());
					}
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function isContentAuthor($commentOrResponse) {
		$article = ViewableArticleRuntimeCache::getInstance()->getObject($this->getObjectID($commentOrResponse));
		return $commentOrResponse->userID && $article->userID == $commentOrResponse->userID;
	}
}
