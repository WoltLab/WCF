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
 * @extends AbstractSitemapObjectObjectType<ArticleContentList>
 */
class ArticleSitemapObject extends AbstractSitemapObjectObjectType
{
    #[\Override]
    public function getObjectClass()
    {
        return ArticleContent::class;
    }

    #[\Override]
    public function getObjectList()
    {
        $articleContentList = new ArticleContentList();
        $articleContentList->sqlSelects = 'article.lastUpdateTime';
        $articleContentList->sqlConditionJoins = '
            INNER JOIN  wcf1_article article
            ON          article.articleID = article_content.articleID';
        $articleContentList->sqlJoins = $articleContentList->sqlConditionJoins;

        return $articleContentList;
    }

    #[\Override]
    public function getLastModifiedColumn(): string
    {
        return 'lastUpdateTime';
    }

    #[\Override]
    public function canView(DatabaseObject $object)
    {
        \assert($object instanceof ArticleContent);

        return $object->getArticle()->canRead();
    }

    #[\Override]
    public function isAvailableType()
    {
        if (\MODULE_ARTICLE === 0) {
            return false;
        }

        return PageCache::getInstance()->getPageByIdentifier('com.woltlab.wcf.Article')->allowSpidersToIndex !== 0;
    }
}
