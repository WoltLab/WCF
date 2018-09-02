<?php
namespace wcf\system\user\notification\event;
use wcf\data\page\PageCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\email\Email;
use wcf\system\user\notification\object\CommentUserNotificationObject;

/**
 * User notification event for page comments.
 *
 * @author	Joshua Rusweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since       3.2
 *
 * @method	CommentUserNotificationObject	getUserNotificationObject()
 */
class PageCommentResponseOwnerUserNotificationEvent extends AbstractSharedUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableCommentResponseUserNotificationEvent;
	use TTestablePageUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.pageComment.responseOwner.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.pageComment.responseOwner.title');
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
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.pageComment.responseOwner.message.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'commentID' => $this->getUserNotificationObject()->commentID,
				'page' => PageCache::getInstance()->getPage($this->additionalData['objectID']),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.pageComment.responseOwner.message', [
			'author' => $this->author,
			'commentID' => $this->getUserNotificationObject()->commentID,
			'page' => PageCache::getInstance()->getPage($this->additionalData['objectID']),
			'responseID' => $this->getUserNotificationObject()->responseID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$messageID = '<com.woltlab.wcf.user.pageComment.notification/'.$this->getUserNotificationObject()->commentID.'@'.Email::getHost().'>';
		
		return [
			'template' => 'email_notification_commentResponseOwner',
			'in-reply-to' => [$messageID],
			'references' => [$messageID],
			'application' => 'wcf',
			'variables' => [
				'commentID' => $this->getUserNotificationObject()->commentID,
				'page' => PageCache::getInstance()->getPage($this->additionalData['objectID']),
				'languageVariablePrefix' => 'wcf.user.notification.pageComment.responseOwner',
				'responseID' => $this->getUserNotificationObject()->responseID
			]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return PageCache::getInstance()->getPage($this->additionalData['objectID'])->getLink() . '#comment'. $this->getUserNotificationObject()->commentID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->notification->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author) {
		return [
			'objectID' => self::getTestPage()->pageID,
			'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page')
		];
	}
}
