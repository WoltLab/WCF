<?php
namespace wcf\system\user\notification\event;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * User notification event for moderation queue commments.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.0
 */
class ModerationQueueCommentResponseUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * language item prefix for the notification texts
	 * @var	string
	 */
	protected $languageItemPrefix = null;
	
	/**
	 * moderation queue object the notifications (indirectly) belong to
	 * @var	ViewableModerationQueue
	 */
	protected $moderationQueue = null;
	
	/**
	 * true if the moderation queue is already loaded
	 * @var	boolean
	 */
	protected $moderationQueueLoaded = false;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		if (!WCF::getSession()->getPermission('mod.general.canUseModeration') || $this->getModerationQueue() ===  null) {
			return false;
		}
		
		return $this->getModerationQueue()->canEdit();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable($this->getLanguageItemPrefix().'.commentResponse.mail.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'notificationType' => $notificationType,
				'others' => $count - 1,
				'moderationQueue' => $this->getModerationQueue(),
				'response' => $this->userNotificationObject
			]);
		}
		
		$comment = CommentRuntimeCache::getInstance()->getObject($this->userNotificationObject->commentID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		
		return $this->getLanguage()->getDynamicVariable($this->getLanguageItemPrefix().'.commentResponse.mail', [
			'author' => $this->author,
			'commentAuthor' => $commentAuthor,
			'moderationQueue' => $this->getModerationQueue(),
			'notificationType' => $notificationType,
			'response' => $this->userNotificationObject
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getModerationQueue()->queueID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getModerationQueue()->getLink() . '#comments';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable($this->getLanguageItemPrefix().'.commentResponse.message.stacked', [
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'moderationQueue' => $this->getModerationQueue()
			]);
		}
		
		$comment = CommentRuntimeCache::getInstance()->getObject($this->userNotificationObject->commentID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		
		return $this->getLanguage()->getDynamicVariable($this->getLanguageItemPrefix().'.commentResponse.message', [
			'author' => $this->author,
			'commentAuthor' => $commentAuthor,
			'moderationQueue' => $this->getModerationQueue()
		]);
	}
	
	/**
	 * Returns the moderation queue object the responded to comment belongs to.
	 * Returns null if the active user has no access to the moderation queue.
	 * 
	 * @return	ViewableModerationQueue
	 */
	public function getModerationQueue() {
		if (!$this->moderationQueueLoaded) {
			$comment = CommentRuntimeCache::getInstance()->getObject($this->userNotificationObject->commentID);
			
			$this->moderationQueue = ViewableModerationQueue::getViewableModerationQueue($comment->objectID);
			$this->moderationQueueLoaded = true;
		}
		
		return $this->moderationQueue;
	}
	
	/**
	 * Returns the language item prefix for the notification texts.
	 * 
	 * @return	string
	 */
	public function getLanguageItemPrefix() {
		if ($this->languageItemPrefix === null) {
			/** @var IModerationQueueReportHandler $moderationHandler */
			$moderationHandler = ObjectTypeCache::getInstance()->getObjectType($this->getModerationQueue()->objectTypeID)->getProcessor();
			$this->languageItemPrefix = $moderationHandler->getCommentNotificationLanguageItemPrefix();
		}
		
		return $this->languageItemPrefix;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable($this->getLanguageItemPrefix().'.commentResponse.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get($this->getLanguageItemPrefix().'.commentResponse.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		CommentRuntimeCache::getInstance()->cacheObjectID($this->userNotificationObject->commentID);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['userID']);
	}
}
