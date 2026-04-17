<?php

namespace wcf\system\attachment;

use wcf\data\article\Article;
use wcf\data\article\ArticleList;
use wcf\system\WCF;

/**
 * Attachment object type implementation for cms articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 *
 * @extends AbstractAttachmentObjectType<Article>
 */
class ArticleAttachmentObjectType extends AbstractAttachmentObjectType
{
    #[\Override]
    public function canDownload(int $objectID)
    {
        if ($objectID) {
            return (new Article($objectID))->canRead();
        }

        return false;
    }

    #[\Override]
    public function canUpload(int $objectID, int $parentObjectID = 0)
    {
        if ($objectID) {
            return (new Article($objectID))->canEdit();
        }

        return WCF::getSession()->hasPermission('admin.content.article.canManageArticle')
            || WCF::getSession()->hasPermission('admin.content.article.canContributeArticle');
    }

    #[\Override]
    public function canDelete(int $objectID)
    {
        return $this->canUpload($objectID);
    }

    #[\Override]
    public function cacheObjects(array $objectIDs)
    {
        $articleList = new ArticleList();
        $articleList->setObjectIDs(\array_unique($objectIDs));
        $articleList->readObjects();

        foreach ($articleList->getObjects() as $objectID => $object) {
            $this->cachedObjects[$objectID] = $object;
        }
    }
}
