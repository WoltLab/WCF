<?php

namespace wcf\system\sitemap\object;

use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentList;
use wcf\data\DatabaseObject;
use wcf\data\page\PageCache;

/**
 * Article sitemap implementation.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractSitemapObjectObjectType<ArticleContent, ArticleContentList>
 */
class ArticleSitemapObject extends AbstractSitemapObjectObjectType
{
    #[\Override]
    public function getObjectClass()
    {
        return ArticleContent::class;
    }

    #[\Override]
    public function canView(DatabaseObject $object)
    {
        return $object->getArticle()->canRead();
    }

    #[\Override]
    public function isAvailableType()
    {
        if (!MODULE_ARTICLE) {
            return false;
        }

        return !!PageCache::getInstance()->getPageByIdentifier('com.woltlab.wcf.Article')->allowSpidersToIndex;
    }
}
