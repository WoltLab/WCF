<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\email\Email;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\WCF;

/**
 * User notification event for profile's owner for comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 *
 * @method	CommentResponseUserNotificationObject	getUserNotificationObject()
 */
class UserProfileCommentResponseOwnerUserNotificationEvent extends AbstractSharedUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableCommentResponseUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['userID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.commentResponseOwner.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.message.stacked', [
				'author' => $commentAuthor,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered,
				'commentID' => $this->getUserNotificationObject()->commentID
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.message', [
			'author' => $this->author,
			'commentAuthor' => $commentAuthor,
			'commentID' => $this->getUserNotificationObject()->commentID,
			'responseID' => $this->getUserNotificationObject()->responseID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		
		$messageID = '<com.woltlab.wcf.user.profileComment.notification/'.$comment->commentID.'@'.Email::getHost().'>';
		
		return [
			'template' => 'email_notification_commentResponseOwner',
			'application' => 'wcf',
			'in-reply-to' => [$messageID],
			'references' => [$messageID],
			'variables' => [
				'commentAuthor' => $commentAuthor,
				'commentID' => $this->getUserNotificationObject()->commentID,
				'owner' => $owner,
				'responseID' => $this->getUserNotificationObject()->responseID,
				'languageVariablePrefix' => 'wcf.user.notification.commentResponseOwner'
			]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink(
			'User',
			['object' => WCF::getUser()],
			'#wall/comment' . $this->getUserNotificationObject()->commentID
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.1
	 */
	protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author) {
		return [
			'objectID' => $recipient->userID,
			'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment')
		];
	}
}
