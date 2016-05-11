<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\like\Like;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for comment responses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 * 
 * @method	CommentResponse		getDecoratedObject()
 * @mixin	CommentResponse
 */
class LikeableCommentResponse extends AbstractLikeObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CommentResponse::class;
	
	/**
	 * @inheritDoc
	 */
	public function getObjectType() {
		if ($this->objectType === null) {
			$this->objectType = ObjectTypeCache::getInstance()->getObjectType($this->getDecoratedObject()->objectTypeID);
		}
		
		return $this->objectType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->message;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return $this->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendNotification(Like $like) {
		$comment = new Comment($this->object->commentID);
		$objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID);
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.like.notification')) {
			$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.response.like.notification');
			if ($this->userID != WCF::getUser()->userID) {
				$notificationObject = new LikeUserNotificationObject($like);
				UserNotificationHandler::getInstance()->fireEvent('like', $objectType->objectType.'.response.like.notification', $notificationObject, [$this->userID], [
					'commentID' => $comment->commentID,
					'commentUserID' => $comment->userID,
					'objectID' => $comment->objectID
				]);
			}
		}
	}
}
