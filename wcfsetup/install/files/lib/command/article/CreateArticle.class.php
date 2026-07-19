<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\data\article\ArticleEditor;
use wcf\system\search\SearchIndexManager;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\ArticleUserNotificationObject;
use wcf\system\user\object\watch\UserObjectWatchHandler;

/**
 * Creates a new article.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateArticle
{
    public function __construct(
        private readonly ArticleBuilder $builder,
    ) {}

    public function __invoke(): Article
    {
        $article = $this->builder->create();

        $this->updateSearchIndex($article);

        (new ResetUserStorageForUnreadArticles())();

        if ($article->publicationStatus == Article::PUBLISHED) {
            ArticleEditor::updateArticleCounter([$article->userID => 1]);

            UserObjectWatchHandler::getInstance()->updateObject(
                'com.woltlab.wcf.article.category',
                $article->getCategory()->categoryID,
                'article',
                'com.woltlab.wcf.article.notification',
                new ArticleUserNotificationObject($article)
            );

            UserActivityEventHandler::getInstance()->fireEvent(
                'com.woltlab.wcf.article.recentActivityEvent',
                $article->articleID,
                null,
                $article->userID,
                $article->time
            );

            (new MarkArticleAsRead($article))();
        }

        return $article;
    }

    private function updateSearchIndex(Article $article): void
    {
        foreach ($article->getArticleContents() as $content) {
            SearchIndexManager::getInstance()->set(
                'com.woltlab.wcf.article',
                $content->articleContentID,
                $content->content ?? '',
                $content->title,
                $article->time,
                $article->userID,
                $article->username,
                $content->languageID,
                $content->teaser
            );
        }
    }
}
