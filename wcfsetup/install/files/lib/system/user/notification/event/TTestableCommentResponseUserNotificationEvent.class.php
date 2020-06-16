<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\comment\CommentEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Default implementation of some methods of the testable user notification event interface
 * for comment response user notificiation events.
 * 
 * As PHP 5.5 does not support abstract static functions in traits, we require them by this documentation:
 * - protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
 * 	returns the `objectID` and `objectTypeID` parameter for comment creation.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableCommentResponseUserNotificationEvent {
	use TTestableUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	public static function canBeTriggeredByGuests() {
		return true;
	}
	
	/**
	 * Creates a test comment response.
	 * 
	 * @param	UserProfile	$recipient
	 * @param	UserProfile	$author
	 * @return	CommentResponse
	 */
	public static function createTestCommentResponse(UserProfile $recipient, UserProfile $author) {
		/** @var Comment $comment */
		$comment = (new CommentAction([], 'create', [
			'data' => array_merge([
				'enableHtml' => 1,
				'isDisabled' => 0,
				'message' => '<p>Test Comment</p>',
				'time' => TIME_NOW - 10,
				'userID' => $recipient->userID,
				'username' => $recipient->username
			], self::getTestCommentObjectData($recipient, $author))
		]))->executeAction()['returnValues'];
		
		/** @var ICommentManager $commentManager */
		$commentManager = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID)->getProcessor();
		$commentManager->updateCounter($comment->objectID, 1);
		
		/** @var CommentResponse $commentResponse */
		$commentResponse = (new CommentResponseAction([], 'create', [
			'data' => [
				'commentID' => $comment->commentID,
				'time' => TIME_NOW - 10,
				'userID' => $author->userID,
				'username' => $author->username,
				'message' => 'Test Response',
				'isDisabled' => 0
			]
		]))->executeAction()['returnValues'];
		
		$commentResponse->setComment($comment);
		
		$commentEditor = new CommentEditor($comment);
		$commentEditor->updateResponseIDs();
		$commentEditor->updateUnfilteredResponseIDs();
		
		return $commentResponse;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object) {
		/** @var CommentResponseUserNotificationObject $object */
		
		return [
			'commentID' => $object->commentID,
			'objectID' => $object->getComment()->objectID,
			'objectUserID' => $object->getComment()->objectID,
			'userID' => $object->getComment()->userID
		];
	}
	
	/**
	 * @inheritDoc
	 * @return	CommentResponseUserNotificationObject[]
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		return [new CommentResponseUserNotificationObject(self::createTestCommentResponse($recipient, $author))];
	}
}
