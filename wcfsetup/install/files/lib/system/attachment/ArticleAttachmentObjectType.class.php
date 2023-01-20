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
 * @method      Article     getObject($objectID)
 */
class ArticleAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDownload($objectID)
    {
        if ($objectID) {
            return (new Article($objectID))->canRead();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canUpload($objectID, $parentObjectID = 0)
    {
        if ($objectID) {
            return (new Article($objectID))->canEdit();
        }

        return WCF::getSession()->getPermission('admin.content.article.canManageArticle')
            || WCF::getSession()->getPermission('admin.content.article.canContributeArticle');
    }

    /**
     * @inheritDoc
     */
    public function canDelete($objectID)
    {
        return $this->canUpload($objectID);
    }

    /**
     * @inheritDoc
     */
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
