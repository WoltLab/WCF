<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\WCF;

/**
 * User notification event for profile comment likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * 
 * @method	LikeUserNotificationObject	getUserNotificationObject()
 */
class UserProfileCommentLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableCommentLikeUserNotificationEvent;
	use TReactionUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.comment.like.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		$owner = null;
		if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
			$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		}
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.message.stacked', [
				'author' => $this->author,
				'authors' => $authors,
				'commentID' => $this->getCommentID(),
				'count' => $count,
				'others' => $count - 1,
				'owner' => $owner,
				'reactions' => $this->getReactionsForAuthors()
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.message', [
			'author' => $this->author,
			'commentID' => $this->getCommentID(),
			'owner' => $owner, 
			'userNotificationObject' => $this->getUserNotificationObject(),
			'reactions' => $this->getReactionsForAuthors()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		throw new \LogicException('Unreachable');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$owner = WCF::getUser();
		if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
			$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		}
		
		return $owner->getLink() . '#wall/comment' . $this->getCommentID();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getCommentID());
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
	
	/**
	 * Returns the liked comment's id.
	 * 
	 * @return      integer
	 */
	protected function getCommentID() {
		// this is the `wcfN_like.objectID` value
		return $this->getUserNotificationObject()->objectID;
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
