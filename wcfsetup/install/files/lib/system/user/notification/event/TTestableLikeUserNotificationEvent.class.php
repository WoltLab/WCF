<?php
namespace wcf\system\user\notification\event;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\Like;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\like\LikeHandler;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\WCF;

/**
 * Default implementation of some methods of the testable user notification event interface
 * for like user notificiation events.
 * 
 * As PHP 5.5 does not support abstract static functions in traits, we require them by this documentation:
 * - protected static function createTestLikeObject(UserProfile $recipient, UserProfile $author)
 * 	creates a likable object
 * - protected static function getTestLikeableObjectTypeName()
 * 	returns the name of the likeable object type name
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableLikeUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	public static function canBeTriggeredByGuests() {
		return false;
	}
	
	/**
	 * Returns the like object for the given user notification object.
	 * 
	 * @param	IUserNotificationObject		$object
	 * @return	ILikeObject
	 */
	protected static function getTestLikeObject(IUserNotificationObject $object) {
		/** @var LikeUserNotificationObject $object */
		
		$oldUser = WCF::getUser();
		
		WCF::getSession()->changeUser(UserRuntimeCache::getInstance()->getObject($object->userID), true);
		
		LikeHandler::getInstance()->loadLikeObjects(
			LikeHandler::getInstance()->getObjectType(self::getTestLikeableObjectTypeName()),
			[$object->objectID]
		);
		
		WCF::getSession()->changeUser($oldUser, true);
		
		return LikeHandler::getInstance()->getLikeObject(
			LikeHandler::getInstance()->getObjectType(self::getTestLikeableObjectTypeName()),
			$object->objectID
		)->getLikedObject();
	}
	
	/**
	 * @inheritDoc
	 * @return	LikeUserNotificationObject[]
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		/** @var ILikeObject $likeObject */
		$likeObject = self::createTestLikeObject($recipient, $author);
		$likeObject->setObjectType(LikeHandler::getInstance()->getObjectType(self::getTestLikeableObjectTypeName()));
		
		/** @var Like $like */
		$like = LikeHandler::getInstance()->like(
			$likeObject,
			$author->getDecoratedObject(),
			Like::LIKE
		)['like'];
		
		return [new LikeUserNotificationObject($like)];
	}
}
