<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\LikeableComment;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Default implementation of some methods of the testable user notification event interface
 * for comment like user notificiation events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableCommentLikeUserNotificationEvent {
	use TTestableCommentUserNotificationEvent;
	use TTestableLikeUserNotificationEvent {
		TTestableLikeUserNotificationEvent::canBeTriggeredByGuests insteadof TTestableCommentUserNotificationEvent;
		TTestableLikeUserNotificationEvent::getTestObjects insteadof TTestableCommentUserNotificationEvent;
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function createTestLikeObject(UserProfile $recipient, UserProfile $author) {
		return new LikeableComment(self::createTestComment($recipient, $author));
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object) {
		/** @var LikeableComment $likedObject */
		$likedObject = self::getTestLikeObject($object);
		
		return [
			'objectID' => $likedObject->objectID,
			'objectOwnerID' => $likedObject->getUserID()
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getTestLikeableObjectTypeName() {
		return 'com.woltlab.wcf.comment';
	}
}
