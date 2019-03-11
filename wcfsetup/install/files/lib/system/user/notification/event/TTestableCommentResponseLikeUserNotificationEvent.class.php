<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\response\LikeableCommentResponse;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Default implementation of some methods of the testable user notification event interface
 * for comment response like user notificiation events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableCommentResponseLikeUserNotificationEvent {
	use TTestableCommentResponseUserNotificationEvent;
	use TTestableLikeUserNotificationEvent {
		TTestableLikeUserNotificationEvent::canBeTriggeredByGuests insteadof TTestableCommentResponseUserNotificationEvent;
		TTestableLikeUserNotificationEvent::getTestObjects insteadof TTestableCommentResponseUserNotificationEvent;
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function createTestLikeObject(UserProfile $recipient, UserProfile $author) {
		return new LikeableCommentResponse(self::createTestCommentResponse($recipient, $author));
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object) {
		/** @var LikeableCommentResponse $likedObject */
		$likedObject = self::getTestLikeObject($object);
		
		return [
			'commentID' => $likedObject->getComment()->commentID,
			'commentUserID' => $likedObject->getComment()->userID,
			'objectID' => $likedObject->getComment()->objectID
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getTestLikeableObjectTypeName() {
		return 'com.woltlab.wcf.comment.response';
	}
}
