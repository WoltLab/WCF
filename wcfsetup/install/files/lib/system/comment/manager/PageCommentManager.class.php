<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Page comment manager implementation.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment\Manager
 */
class PageCommentManager extends AbstractCommentManager implements IViewableLikeProvider {
	/**
	 * @inheritDoc
	 */
	protected $permissionAdd = 'user.pageComment.canAddComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionDelete = 'user.pageComment.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionEdit = 'user.pageComment.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModDelete = 'mod.pageComment.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModEdit = 'mod.pageComment.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionCanModerate = 'mod.pageComment.canModerateComment';
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// check object id
		$page = new Page($objectID);
		if (!$page->pageID || !$page->isAccessible()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getCmsLink($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('wcf.page.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.comment');
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {}
	
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
		$pageIDs = [];
		foreach ($comments as $comment) {
			$pageIDs[] = $comment->objectID;
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects(array_unique($userIDs));
		}
		
		// fetch pages
		$pages = [];
		if (!empty($pageIDs)) {
			$pageList = new PageList();
			$pageList->setObjectIDs($pageIDs);
			$pageList->readObjects();
			$pages = $pageList->getObjects();
		}
		
		// set message
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				// comment like
				if (isset($comments[$like->objectID])) {
					$comment = $comments[$like->objectID];
					
					if (isset($pages[$comment->objectID]) && $pages[$comment->objectID]->isAccessible()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.pageComment', [
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'page' => $pages[$comment->objectID],
							'like' => $like
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
					
					if (isset($pages[$comment->objectID]) && $pages[$comment->objectID]->isAccessible()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.pageComment.response', [
							'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'page' => $pages[$comment->objectID],
							'like' => $like
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($response->getExcerpt());
					}
				}
			}
		}
	}
}
