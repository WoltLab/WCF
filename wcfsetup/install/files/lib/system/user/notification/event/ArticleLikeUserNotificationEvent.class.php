<?php
namespace wcf\system\user\notification\event;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\LikeableArticle;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * User notification event for post likes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	WoltLab License <http://www.woltlab.com/license-agreement.html>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	5.3
 * 
 * @method	LikeUserNotificationObject	getUserNotificationObject()
 */
class ArticleLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableLikeUserNotificationEvent {
		TTestableLikeUserNotificationEvent::canBeTriggeredByGuests insteadof TTestableUserNotificationEvent;
	}
	use TTestableArticleUserNotificationEvent;
	use TTestableCategorizedUserNotificationEvent;
	use TTestableUserNotificationEvent;
	use TReactionUserNotificationEvent;
	
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
			return $this->getLanguage()->getDynamicVariable('wcf.article.like.notification.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.article.like.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$article = ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID);
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.article.like.notification.message.stacked', [
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1,
				'article' => $article,
				'reactions' => $this->getReactionsForAuthors(),
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.article.like.notification.message', [
			'author' => $this->author,
			'article' => $article,
			'userNotificationObject' => $this->getUserNotificationObject(),
			'reactions' => $this->getReactionsForAuthors(),
		]);
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		// not supported
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID)->getLink();
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->additionalData['objectID']);
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		if (!ViewableArticleRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID)->canRead()) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'wbbUnreadWatchedThreads');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadArticles');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadWatchedArticles');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadArticlesByCategory');
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function createTestLikeObject(UserProfile $recipient, UserProfile $author) {
		return new LikeableArticle(self::getTestArticle(self::createTestCategory(ArticleCategory::OBJECT_TYPE_NAME), $author));
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getTestLikeableObjectTypeName() {
		return 'com.woltlab.wcf.likeableArticle';
	}
}
