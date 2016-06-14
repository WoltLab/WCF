<?php
namespace wcf\system\user\notification\event;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserProfile;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\user\notification\object\IUserNotificationObject;
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
class ModerationQueueCommentUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * language item prefix for the notification texts
	 * @var	string
	 */
	protected $languageItemPrefix = '';
	
	/**
	 * moderation queue object the notifications (indirectly) belong to
	 * @var	ViewableModerationQueue
	 */
	protected $moderationQueue = null;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		if ($this->moderationQueue === null || !WCF::getSession()->getPermission('mod.general.canUseModeration')) {
			return false;
		}
		
		return $this->moderationQueue->canEdit();
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
			
			return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix.'.comment.mail.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'moderationQueue' => $this->moderationQueue,
				'notificationType' => $notificationType
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix.'.comment.mail', [
			'comment' => $this->userNotificationObject,
			'author' => $this->author,
			'moderationQueue' => $this->moderationQueue,
			'notificationType' => $notificationType
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->moderationQueue->queueID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->moderationQueue->getLink() . '#comments';
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
			
			return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix.'.comment.message.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'moderationQueue' => $this->moderationQueue
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix.'.comment.message', [
			'author' => $this->author,
			'moderationQueue' => $this->moderationQueue
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix.'.comment.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get($this->languageItemPrefix.'.comment.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function setObject(UserNotification $notification, IUserNotificationObject $object, UserProfile $author, array $additionalData = []) {
		parent::setObject($notification, $object, $author, $additionalData);
		
		// if the active user has no access, $this->moderationQueue is null
		$this->moderationQueue = ViewableModerationQueue::getViewableModerationQueue($this->userNotificationObject->objectID);
		
		if ($this->moderationQueue) {
			/** @var IModerationQueueHandler $moderationHandler */
			$moderationHandler = ObjectTypeCache::getInstance()->getObjectType($this->moderationQueue->objectTypeID)->getProcessor();
			$this->languageItemPrefix = $moderationHandler->getCommentNotificationLanguageItemPrefix();
		}
	}
}
