<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User profile comment manager implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment.manager
 * @category	Community Framework
 */
class UserProfileCommentManager extends AbstractCommentManager implements IViewableLikeProvider {
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionAdd
	 */
	protected $permissionAdd = 'user.profileComment.canAddComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionCanModerate
	 */
	protected $permissionCanModerate = 'mod.profileComment.canModerateComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionDelete
	 */
	protected $permissionDelete = 'user.profileComment.canDeleteComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionEdit
	 */
	protected $permissionEdit = 'user.profileComment.canEditComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionModDelete
	 */
	protected $permissionModDelete = 'mod.profileComment.canDeleteComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionModEdit
	 */
	protected $permissionModEdit = 'mod.profileComment.canEditComment';
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::isAccessible()
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// check object id
		$userProfile = UserProfile::getUserProfile($objectID);
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
	 * @see	\wcf\system\comment\manager\ICommentManager::getLink()
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('User', array('id' => $objectID));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::getTitle()
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('wcf.user.profile.content.wall.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('wcf.user.profile.content.wall.comment');
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::updateCounter()
	 */
	public function updateCounter($objectID, $value) {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canDeleteComment()
	 */
	public function canDeleteComment(Comment $comment) {
		if ($comment->objectID == WCF::getUser()->userID && WCF::getSession()->getPermission('user.profileComment.canDeleteCommentInOwnProfile')) {
			return true;
		}
		
		return parent::canDeleteComment($comment);
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canDeleteResponse()
	 */
	public function canDeleteResponse(CommentResponse $response) {
		if ($response->getComment()->objectID == WCF::getUser()->userID && WCF::getSession()->getPermission('user.profileComment.canDeleteCommentInOwnProfile')) {
			return true;
		}
		
		return parent::canDeleteResponse($response);
	}
	
	/**
	 * @see	\wcf\system\like\IViewableLikeProvider::prepare()
	 */
	public function prepare(array $likes) {
		if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
			return;
		}
		
		$commentLikeObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');
		
		$commentIDs = $responseIDs = array();
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				$commentIDs[] = $like->objectID;
			}
			else {
				$responseIDs[] = $like->objectID;
			}
		}
		
		// fetch response
		$userIDs = $responses = array();
		if (!empty($responseIDs)) {
			$responseList = new CommentResponseList();
			$responseList->getConditionBuilder()->add("comment_response.responseID IN (?)", array($responseIDs));
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
		$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($commentIDs));
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$users = array();
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
		}
		if (!empty($userIDs)) {
			$users = UserProfile::getUserProfiles(array_unique($userIDs));
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
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.user.profileComment', array(
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'user' => $users[$comment->objectID],
							'like' => $like
						));
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
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.user.profileComment.response', array(
							'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
							'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
							'user' => $users[$comment->objectID],
							'like' => $like
						));
						$like->setTitle($text);
						
						// output
						$like->setDescription($response->getExcerpt());
					}
				}
			}
		}
	}
}
