<?php

namespace wcf\system\user\notification\event;

use wcf\data\article\category\ArticleCategory;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\ArticleUserNotificationObject;

/**
 * Notification event for new articles.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ArticleUserNotificationObject   getUserNotificationObject()
 */
class ArticleUserNotificationEvent extends AbstractUserNotificationEvent implements ITestableUserNotificationEvent
{
    use TTestableUserNotificationEvent;
    use TTestableArticleUserNotificationEvent;
    use TTestableCategorizedUserNotificationEvent;

    #[\Override]
    public function getTitle(): string
    {
        return $this->getLanguage()->get('wcf.user.notification.article.title');
    }

    #[\Override]
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.article.message', [
            'article' => $this->userNotificationObject,
            'author' => $this->author,
        ]);
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant'): array
    {
        if ($this->getUserNotificationObject()->isMultilingual) {
            $articleContent = $this->getUserNotificationObject()
                ->getArticleContents()[$this->getLanguage()->languageID];
        } else {
            $articleContent = $this->getUserNotificationObject()
                ->getArticleContents()[0];
        }

        return [
            'message-id' => 'com.woltlab.wcf.article/' . $this->getUserNotificationObject()->articleID,
            'template' => 'email_notification_article',
            'application' => 'wcf',
            'variables' => [
                'article' => $this->getUserNotificationObject(),
                'articleContent' => $articleContent,
                'languageVariablePrefix' => 'wcf.user.notification.article',
                'author' => $this->author,
            ],
        ];
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->getUserNotificationObject()->getLink();
    }

    #[\Override]
    public function checkAccess()
    {
        return $this->getUserNotificationObject()->canRead();
    }

    #[\Override]
    public static function canBeTriggeredByGuests()
    {
        return true;
    }

    /**
     * @return  ArticleUserNotificationObject[]
     */
    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        return [
            new ArticleUserNotificationObject(
                self::getTestArticle(self::createTestCategory(ArticleCategory::OBJECT_TYPE_NAME), $author)
            ),
        ];
    }
}
