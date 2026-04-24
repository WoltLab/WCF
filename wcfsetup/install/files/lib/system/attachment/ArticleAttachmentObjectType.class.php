<?php

namespace wcf\system\attachment;

use wcf\data\article\Article;
use wcf\data\article\content\ArticleContent;
use wcf\system\cache\runtime\ArticleContentRuntimeCache;
use wcf\system\cache\runtime\ArticleRuntimeCache;
use wcf\system\WCF;

/**
 * Attachment object type implementation for cms articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 *
 * @extends AbstractAttachmentObjectType<ArticleContent>
 */
class ArticleAttachmentObjectType extends AbstractAttachmentObjectType
{
    #[\Override]
    public function canDownload(int $objectID): bool
    {
        if ($objectID !== 0) {
            $article = $this->getArticleByContentID($objectID);
            if ($article !== null && $article->canRead()) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function canUpload(int $objectID, int $parentObjectID = 0): bool
    {
        if ($objectID !== 0) {
            $article = $this->getArticleByContentID($objectID);
            if ($article !== null && $article->canEdit()) {
                return true;
            }
        }

        return WCF::getSession()->hasPermission('admin.content.article.canManageArticle')
            || WCF::getSession()->hasPermission('admin.content.article.canContributeArticle');
    }

    #[\Override]
    public function canDelete(int $objectID): bool
    {
        return $this->canUpload($objectID);
    }

    #[\Override]
    protected function getObjectRuntimeCache(): ArticleContentRuntimeCache
    {
        return ArticleContentRuntimeCache::getInstance();
    }

    private function getArticleByContentID(int $articleContentID): ?Article
    {
        $content = ArticleContentRuntimeCache::getInstance()->getObject($articleContentID);
        if ($content === null) {
            return null;
        }

        return ArticleRuntimeCache::getInstance()->getObject($content->articleID);
    }
}
