<?php

namespace wcf\command\article;

use wcf\command\reaction\DeleteObjectReactions;
use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Deletes an article and all of its associated data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteArticle
{
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        $articleContentIDs = $attachmentArticleContentIDs = [];
        foreach ($this->article->getArticleContents() as $articleContent) {
            $articleContentIDs[] = $articleContent->articleContentID;

            if ($articleContent->attachments) {
                $attachmentArticleContentIDs[] = $articleContent->articleContentID;
            }
        }

        ArticleBuilder::delete($this->article);

        // delete like data
        new DeleteObjectReactions('com.woltlab.wcf.likeableArticle', [$this->article->articleID])();
        // delete comments
        CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.articleComment', $articleContentIDs);
        // delete tag to object entries
        TagEngine::getInstance()->deleteObjects('com.woltlab.wcf.article', $articleContentIDs);
        // delete entry from search index
        SearchIndexManager::getInstance()->delete('com.woltlab.wcf.article', $articleContentIDs);
        // delete user notifications
        UserNotificationHandler::getInstance()->removeNotifications(
            'com.woltlab.wcf.article.notification',
            [$this->article->articleID]
        );
        // delete recent activity events
        UserActivityEventHandler::getInstance()->removeEvents(
            'com.woltlab.wcf.article.recentActivityEvent',
            [$this->article->articleID]
        );
        // delete embedded object references
        MessageEmbeddedObjectManager::getInstance()->removeObjects(
            'com.woltlab.wcf.article.content',
            $articleContentIDs
        );
        // update wcf1_user.articles
        if ($this->article->publicationStatus == Article::PUBLISHED) {
            if ($this->article->userID !== null) {
                ArticleBuilder::incrementArticleCounter($this->article->userID, -1);
            }
        }
        // delete attachments
        if ($attachmentArticleContentIDs !== []) {
            AttachmentHandler::removeAttachments(
                'com.woltlab.wcf.article.content',
                $attachmentArticleContentIDs
            );
        }
    }
}
