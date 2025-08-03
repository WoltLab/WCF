<?php

namespace wcf\system\cache\eager;

use wcf\data\category\CategoryList;
use wcf\system\cache\eager\data\CategoryCacheData;

/**
 * Eager cache implementation for categories of a specific object type.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractEagerCache<CategoryCacheData>
 */
final class CategoryCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): CategoryCacheData
    {
        $categoryList = new CategoryList();
        $categoryList->sqlSelects = "object_type.objectType";
        $categoryList->sqlJoins = "
            LEFT JOIN wcf1_object_type object_type
            ON        object_type.objectTypeID = category.objectTypeID";
        $categoryList->sqlOrderBy = "category.showOrder";
        $categoryList->readObjects();

        $objectTypeCategoryIDs = [];
        foreach ($categoryList->getObjects() as $category) {
            $objectType = $category->objectType;

            if (!isset($objectTypeCategoryIDs[$objectType])) {
                $objectTypeCategoryIDs[$objectType] = [];
            }

            $objectTypeCategoryIDs[$objectType][] = $category->categoryID;
        }

        return new CategoryCacheData(
            $categoryList->getObjects(),
            $objectTypeCategoryIDs
        );
    }
}
