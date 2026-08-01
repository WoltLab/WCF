<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\event\article\ArticlePublished;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\ArticleUserNotificationObject;
use wcf\system\user\object\watch\UserObjectWatchHandler;

/**
 * Publish an article.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PublishArticle
{
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        ArticleBuilder::forUpdate($this->article)
            ->setTime(\TIME_NOW)
            ->setPublicationStatus(Article::PUBLISHED)
            ->setPublicationDate(0)
            ->update();

        $this->updateUserWatch($this->article);
        $this->addUserActivity($this->article->articleID, $this->article->userID);

        if ($this->article->userID !== null) {
            ArticleBuilder::incrementArticleCounter($this->article->userID, 1);
        }

        new ResetUserStorageForUnreadArticles()();

        $event = new ArticlePublished($this->article);
        EventHandler::getInstance()->fire($event);
    }

    private function updateUserWatch(Article $article): void
    {
        UserObjectWatchHandler::getInstance()->updateObject(
            'com.woltlab.wcf.article.category',
            $article->getCategory()->categoryID,
            'article',
            'com.woltlab.wcf.article.notification',
            new ArticleUserNotificationObject($article)
        );
    }

    private function addUserActivity(int $articleID, int $userID): void
    {
        UserActivityEventHandler::getInstance()->fireEvent(
            'com.woltlab.wcf.article.recentActivityEvent',
            $articleID,
            null,
            $userID,
            \TIME_NOW
        );
    }
}
