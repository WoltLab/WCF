<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User profile comment manager implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment.manager
 * @category	Community Framework
 */
class UserProfileCommentManager extends AbstractCommentManager implements IViewableLikeProvider {
	/**
	 * @inheritDoc
	 */
	protected $permissionAdd = 'user.profileComment.canAddComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionCanModerate = 'mod.profileComment.canModerateComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionDelete = 'user.profileComment.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionEdit = 'user.profileComment.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModDelete = 'mod.profileComment.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModEdit = 'mod.profileComment.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// check object id
		$userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);
		if ($userProfile === null) {
			return false;
		}
		
		// check visibility
		if ($userProfile->isProtected()) {
			return false;
		}
		
		// check target user settings
		if ($validateWritePermission) {
			if (!$userProfile->isAccessible('canWriteProfileComments') && $userProfile->userID != WCF::getUser()->userID) {
				return false;
			}
			
			if ($userProfile->isIgnoredUser(WCF::getUser()->userID)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('User', ['id' => $objectID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('wcf.user.profile.content.wall.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('wcf.user.profile.content.wall.comment');
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteComment(Comment $comment) {
		if ($comment->objectID == WCF::getUser()->userID && WCF::getSession()->getPermission('user.profileComment.canDeleteCommentInOwnProfile')) {
			return true;
		}
		
		return parent::canDeleteComment($comment);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteResponse(CommentResponse $response) {
		if ($response->getComment()->objectID == WCF::getUser()->userID && WCF::getSession()->getPermission('user.profileComment.canDeleteCommentInOwnProfile')) {
			return true;
		}
		
		return parent::canDeleteResponse($response);
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepare(array $likes) {
		if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
			return;
		}
		
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
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects(array_unique($userIDs));
		}
		
		// set message
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				// comment like
				if (isset($comments[$like->objectID])) {
					$comment = $comments[$like->objectID];
					
					if (isset($users[$comment->objectID]) && !$users[$comment->objectID]->isProtected()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.user.profileComment', [
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'user' => $users[$comment->objectID],
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
					
					if (isset($users[$comment->objectID]) && !$users[$comment->objectID]->isProtected()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.user.profileComment.response', [
							'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'user' => $users[$comment->objectID],
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
