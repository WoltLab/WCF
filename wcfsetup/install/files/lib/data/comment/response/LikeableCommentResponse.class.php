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
 * Likeable object implementation for comment reponses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class LikeableCommentResponse extends AbstractLikeObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getObjectType()
	 */
	public function getObjectType() {
		if ($this->objectType === null) {
			$this->objectType = ObjectTypeCache::getInstance()->getObjectType($this->getDecoratedObject()->objectTypeID);
		}
		
		return $this->objectType;
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->message;
	}
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getURL()
	 */
	public function getURL() {
		return $this->getLink();
	}
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getUserID()
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::sendNotification()
	 */
	public function sendNotification(Like $like) {
		$comment = new Comment($this->object->commentID);
		$objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID);
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.like.notification')) {
			$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.response.like.notification');
			if ($this->userID != WCF::getUser()->userID) {
				$notificationObject = new LikeUserNotificationObject($like);
				UserNotificationHandler::getInstance()->fireEvent('like', $objectType->objectType.'.response.like.notification', $notificationObject, array($this->userID), array(
					'commentID' => $comment->commentID,
					'commentUserID' => $comment->userID,
					'objectID' => $comment->objectID
				));
			}
		}
	}
}
