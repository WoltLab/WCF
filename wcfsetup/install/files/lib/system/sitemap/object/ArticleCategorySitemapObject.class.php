<?php

namespace wcf\system\sitemap\object;

use wcf\data\article\category\ArticleCategory;
use wcf\data\category\CategoryList;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\PageCache;

/**
 * Article category sitemap implementation.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @phpstan-ignore missingType.generics
 */
class ArticleCategorySitemapObject extends AbstractSitemapObjectObjectType
{
    #[\Override]
    public function getObjectClass()
    {
        throw new \LogicException('Unreachable');
    }

    /**
     * @return CategoryList
     */
    #[\Override]
    public function getObjectList()
    {
        $categoryList = new CategoryList();
        $categoryList->decoratorClassName = ArticleCategory::class;
        $categoryList->getConditionBuilder()->add('objectTypeID = ?', [
            ObjectTypeCache::getInstance()
                ->getObjectTypeIDByName('com.woltlab.wcf.category', ArticleCategory::OBJECT_TYPE_NAME),
        ]);

        return $categoryList;
    }

    #[\Override]
    public function canView(DatabaseObject $object)
    {
        /** @var ArticleCategory $object */
        return $object->isAccessible();
    }

    #[\Override]
    public function isAvailableType()
    {
        if (!\MODULE_ARTICLE) {
            return false;
        }

        return !!PageCache::getInstance()
            ->getPageByIdentifier('com.woltlab.wcf.CategoryArticleList')
            ->allowSpidersToIndex;
    }
}
