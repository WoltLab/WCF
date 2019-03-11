<?php
namespace wcf\system\user\notification\event;
use wcf\data\article\category\ArticleCategory;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\user\notification\object\CommentUserNotificationObject;

/**
 * User notification event for article comments.
 *
 * @author	Joshua Rusweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since       5.2
 * 
 * @method	CommentUserNotificationObject	getUserNotificationObject()
 */
class ArticleCommentUserNotificationEvent extends AbstractSharedUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableCommentUserNotificationEvent;
	use TTestableArticleUserNotificationEvent;
	use TTestableCategorizedUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		ViewableArticleRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.title');
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
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.message.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'commentID' => $this->getUserNotificationObject()->commentID,
				'article' => ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.message', [
			'author' => $this->author,
			'commentID' => $this->getUserNotificationObject()->commentID,
			'article' => ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID)
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
			'message-id' => 'com.woltlab.wcf.user.articleComment.notification/'.$this->getUserNotificationObject()->commentID,
			'template' => 'email_notification_comment',
			'application' => 'wcf',
			'variables' => [
				'commentID' => $this->getUserNotificationObject()->commentID,
				'article' => ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID),
				'languageVariablePrefix' => 'wcf.user.notification.articleComment'
			]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID)->getLink() . '#comment'. $this->getUserNotificationObject()->commentID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author) {
		return [
			'objectID' => self::getTestArticle(self::createTestCategory(ArticleCategory::OBJECT_TYPE_NAME), $author)->articleID,
			'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.articleComment')
		];
	}
}
