<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\data\article\ArticleVersionTracker;
use wcf\data\article\content\ArticleContent;
use wcf\system\search\SearchIndexManager;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\ArticleUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Updates the data of an article.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateArticle
{
    public function __construct(
        private readonly ArticleBuilder $builder,
    ) {}

    public function __invoke(): Article
    {
        $oldArticle = $this->builder->getObject();
        $oldStatus = $oldArticle->publicationStatus;
        $oldUserID = $oldArticle->userID;

        // Capture the current content before it is overwritten so that the
        // previous state can be stored as a version.
        $versionData = [];
        $hasChanges = false;
        foreach ($this->builder->articleContentBuilders as $languageID => $articleContentBuilder) {
            $oldContent = ArticleContent::getArticleContent($oldArticle->articleID, $languageID ?: null);
            if ($oldContent === null) {
                $hasChanges = true;
                continue;
            }

            $versionData[] = $oldContent;
            if (
                $oldContent->content != $articleContentBuilder->getContent()
                || $oldContent->teaser != $articleContentBuilder->getTeaser()
                || $oldContent->title != $articleContentBuilder->getTitle()
            ) {
                $hasChanges = true;
            }
        }

        $article = $this->builder->update();

        if ($hasChanges && $versionData !== []) {
            $articleObj = new ArticleVersionTracker($article);
            $articleObj->setContent($versionData);
            VersionTracker::getInstance()->add('com.woltlab.wcf.article', $articleObj);
        }

        if ($this->builder->articleContentBuilders !== []) {
            $this->updateSearchIndex($article);
        }

        (new ResetUserStorageForUnreadArticles())();

        $newStatus = $this->builder->properties['publicationStatus'] ?? $oldStatus;
        if ($newStatus != $oldStatus) {
            $this->handlePublicationStatusChange($article, (int)$oldStatus, (int)$newStatus);
        }

        $newUserID = $this->builder->properties['userID'] ?? $oldUserID;
        if ($newUserID != $oldUserID) {
            $this->updateActivityEventAuthor($article->articleID, (int)$newUserID);
        }

        return $article;
    }

    private function handlePublicationStatusChange(Article $article, int $oldStatus, int $newStatus): void
    {
        if ($newStatus == Article::PUBLISHED || $oldStatus == Article::PUBLISHED) {
            if ($article->userID !== null) {
                ArticleBuilder::incrementArticleCounter($article->userID, $newStatus == Article::PUBLISHED ? 1 : -1);
            }
        }

        if ($newStatus == Article::PUBLISHED) {
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
        } else {
            UserNotificationHandler::getInstance()->removeNotifications(
                'com.woltlab.wcf.article.notification',
                [$article->articleID]
            );
            UserActivityEventHandler::getInstance()->removeEvents(
                'com.woltlab.wcf.article.recentActivityEvent',
                [$article->articleID]
            );
        }
    }

    private function updateActivityEventAuthor(int $articleID, int $userID): void
    {
        $sql = "UPDATE  wcf1_user_activity_event
                SET     userID = ?
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $userID,
            UserActivityEventHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.article.recentActivityEvent'),
            $articleID,
        ]);
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
