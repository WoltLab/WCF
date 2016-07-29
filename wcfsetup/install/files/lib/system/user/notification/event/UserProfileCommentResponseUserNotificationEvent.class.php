<?php
namespace wcf\system\user\notification\event;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\email\Email;

/**
 * User notification event for profile comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 */
class UserProfileCommentResponseUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.commentResponse.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.message.stacked', [
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'owner' => $owner,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.message', [
			'author' => $this->author,
			'owner' => $owner
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		
		$messageID = '<com.woltlab.wcf.user.profileComment.notification/'.$comment->commentID.'@'.Email::getHost().'>';
		
		return [
			'template' => 'email_notification_userProfileCommentResponse',
			'application' => 'wcf',
			'in-reply-to' => [$messageID],
			'references' => [$messageID],
			'variables' => [
				'owner' => $owner
			]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID'])->getLink() . '#wall';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->userNotificationObject->commentID);
	}
}
