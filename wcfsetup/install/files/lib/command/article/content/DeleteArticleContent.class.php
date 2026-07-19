<?php

namespace wcf\command\article\content;

use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentBuilder;
use wcf\event\article\content\ArticleContentDeleted;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\event\EventHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;

/**
 * Deletes an article content permanently.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteArticleContent
{
    public function __construct(
        private readonly ArticleContent $content,
    ) {}

    public function __invoke(): void
    {
        ArticleContentBuilder::delete($this->content);

        $this->cleanupData($this->content);

        EventHandler::getInstance()->fire(new ArticleContentDeleted($this->content));
    }

    private function cleanupData(ArticleContent $content): void
    {
        $contentID = $content->getObjectID();

        CommentHandler::getInstance()->deleteObjects(
            'com.woltlab.wcf.articleComment',
            [$contentID]
        );

        TagEngine::getInstance()->deleteObjects(
            'com.woltlab.wcf.article',
            [$contentID]
        );

        SearchIndexManager::getInstance()->delete(
            'com.woltlab.wcf.article',
            [$contentID]
        );

        MessageEmbeddedObjectManager::getInstance()->removeObjects(
            'com.woltlab.wcf.article.content',
            [$contentID]
        );

        AttachmentHandler::removeAttachments(
            'com.woltlab.wcf.article.content',
            [$contentID]
        );
    }
}
