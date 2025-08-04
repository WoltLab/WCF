<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
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
    public function __construct(public readonly Article $article)
    {
    }

    public function __invoke(): void
    {
        if ($this->article->publicationStatus === Article::PUBLISHED) {
            return;
        }

        (new ArticleEditor($this->article))->update([
            'time' => TIME_NOW,
            'publicationStatus' => Article::PUBLISHED,
            'publicationDate' => 0,
        ]);

        $this->registerArticleActivity();

        (new ResetUserStorageForUnreadArticles())();

        $event = new ArticlePublished($this->article);
        EventHandler::getInstance()->fire($event);
    }

    private function registerArticleActivity(): void
    {
        UserObjectWatchHandler::getInstance()->updateObject(
            'com.woltlab.wcf.article.category',
            $this->article->getCategory()->categoryID,
            'article',
            'com.woltlab.wcf.article.notification',
            new ArticleUserNotificationObject($this->article)
        );

        UserActivityEventHandler::getInstance()->fireEvent(
            'com.woltlab.wcf.article.recentActivityEvent',
            $this->article->articleID,
            null,
            $this->article->userID,
            TIME_NOW
        );

        ArticleEditor::updateArticleCounter([
            $this->article->userID => 1,
        ]);
    }
}
