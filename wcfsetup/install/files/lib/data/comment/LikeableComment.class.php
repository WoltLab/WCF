<?php
namespace wcf\data\comment;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\like\Like;
use wcf\data\reaction\object\IReactionObject;
use wcf\system\comment\CommentHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment
 * 
 * @method	Comment		getDecoratedObject()
 * @mixin	Comment
 */
class LikeableComment extends AbstractLikeObject implements IReactionObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Comment::class;
	
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
		$objectType = CommentHandler::getInstance()->getObjectType($this->getDecoratedObject()->objectTypeID);
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.like.notification') && UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.like.notification')->supportReactions) {
			if ($this->userID != WCF::getUser()->userID) {
				$notificationObject = new LikeUserNotificationObject($like);
				UserNotificationHandler::getInstance()->fireEvent(
					'like',
					$objectType->objectType . '.like.notification',
					$notificationObject,
					[$this->userID],
					[
						'objectID' => $this->getDecoratedObject()->objectID,
						'objectOwnerID' => $this->userID
					]
				);
			}
		}
	}
}
