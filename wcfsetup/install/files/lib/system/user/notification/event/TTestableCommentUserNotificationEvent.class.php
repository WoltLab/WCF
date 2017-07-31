<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Default implementation of some methods of the testable user notification event interface
 * for comment user notificiation events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableCommentUserNotificationEvent {
	use TTestableUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	public static function canBeTriggeredByGuests() {
		return true;
	}
	
	/**
	 * Creates a test comment.
	 * 
	 * @param	UserProfile	$recipient
	 * @param	UserProfile	$author
	 * @return	Comment
	 */
	public static function createTestComment(UserProfile $recipient, UserProfile $author) {
		return (new CommentAction([], 'create', [
			'data' => array_merge([
				'enableHtml' => 1,
				'isDisabled' => 0,
				'message' => '<p>Test Comment</p>',
				'time' => TIME_NOW - 10,
				'userID' => $recipient->userID,
				'username' => $recipient->username
			], self::getTestCommentObjectData($recipient, $author))
		]))->executeAction()['returnValues'];
	}
	
	/**
	 * @see	ITestableUserNotificationEvent::getTestAdditionalData()
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object) {
		/** @var CommentUserNotificationObject $object */
		
		return ['objectUserID' => $object->objectID];
	}
	
	/**
	 * Returns the `objectID` and `objectTypeID` parameter for comment creation.
	 * 
	 * @param	UserProfile	$recipient
	 * @param	UserProfile	$author
	 * @return	array
	 */
	abstract protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author);
	
	/**
	 * @inheritDoc
	 * @return	CommentUserNotificationObject[]
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		return [new CommentUserNotificationObject(self::createTestComment($recipient, $author))];
	}
}
