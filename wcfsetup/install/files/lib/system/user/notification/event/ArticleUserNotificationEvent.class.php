<?php
namespace wcf\system\user\notification\event;
use wcf\data\article\Article;
use wcf\data\article\category\ArticleCategory;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\ArticleUserNotificationObject;

/**
 * Notification event for new articles.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 *
 * @method	ArticleUserNotificationObject	getUserNotificationObject()
 */
class ArticleUserNotificationEvent extends AbstractUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableUserNotificationEvent;
	use TTestableArticleUserNotificationEvent;
	use TTestableCategorizedUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('wcf.user.notification.article.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.article.message', [
			'article' => $this->userNotificationObject,
			'author' => $this->author
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		if ($this->getUserNotificationObject()->isMultilingual) {
			$articleContent = $this->getUserNotificationObject()->getArticleContents()[$this->getLanguage()->languageID];
		}
		else {
			$articleContent = $this->getUserNotificationObject()->getArticleContents()[0];
		}
		
		return [
			'message-id' => 'com.woltlab.wcf.article/'.$this->getUserNotificationObject()->articleID,
			'template' => 'email_notification_article',
			'application' => 'wcf',
			'variables' => [
				'article' => $this->getUserNotificationObject(),
				'articleContent' => $articleContent,
				'languageVariablePrefix' => 'wcf.user.notification.article'
			]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getUserNotificationObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return $this->getUserNotificationObject()->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function canBeTriggeredByGuests() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 * @return	Article[]
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		return [new ArticleUserNotificationObject(self::getTestArticle(self::createTestCategory(ArticleCategory::OBJECT_TYPE_NAME), $author))];
	}
}
