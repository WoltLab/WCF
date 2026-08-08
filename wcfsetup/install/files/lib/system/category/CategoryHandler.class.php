<?php

namespace wcf\system\category;

use wcf\data\category\Category;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\eager\CategoryCache;
use wcf\system\cache\eager\data\CategoryCacheData;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles the categories.
 *
 * @author  Olaf Braun, Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CategoryHandler extends SingletonFactory
{
    private CategoryCacheData $cache;

    /**
     * maps the names of the category object types to the object type ids
     * @var array<int, string>
     */
    private array $objectTypeIDs;

    /**
     * list of category object types
     * @var ObjectType[]
     */
    private array $objectTypes;

    /**
     * Returns all category objects with the given object type. If no object
     * type is given, all categories grouped by object type are returned.
     *
     * @return ($objectType is null ? array<string, array<int, Category>> : array<int, Category>)
     */
    public function getCategories(?string $objectType = null): array
    {
        if ($objectType === null) {
            $categories = [];
            foreach ($this->cache->objectTypeCategoryIDs as $objectType => $categoryIDs) {
                foreach ($categoryIDs as $categoryID) {
                    $categories[$objectType][$categoryID] = $this->cache->getCategory($categoryID);
                }
            }

            return $categories;
        } else {
            return $this->cache->getCategoriesForObjectType($objectType);
        }
    }

    /**
     * Returns the category ids of the given object type.
     *
     * @return int[]
     */
    public function getCategoryIDs(string $objectType): array
    {
        return $this->cache->getCategoryIDsForObjectType($objectType);
    }

    /**
     * Returns the category with the given id or `null` if no such category exists.
     */
    public function getCategory(int $categoryID): ?Category
    {
        return $this->cache->getCategory($categoryID);
    }

    /**
     * Returns the child categories of the category with the given id.
     *
     * The second parameter is only needed if $categoryID is 0.
     *
     * @return  Category[]
     * @throws  SystemException
     */
    public function getChildCategories(int $categoryID, ?int $objectTypeID = null): array
    {
        if ($categoryID === 0 && $objectTypeID === null) {
            throw new SystemException("Missing object type id");
        }

        if ($categoryID !== 0) {
            $objectTypeID = $this->getCategory($categoryID)->objectTypeID;
        }

        $objectType = $this->getObjectType($objectTypeID)->objectType;

        $categories = [];
        foreach ($this->cache->getCategoriesForObjectType($objectType) as $category) {
            if ($category->parentCategoryID === $categoryID) {
                $categories[$category->categoryID] = $category;
            }
        }

        return $categories;
    }

    /**
     * Returns the category object type with the given id or `null` if no such object type exists.
     */
    public function getObjectType(int $objectTypeID): ?ObjectType
    {
        if (isset($this->objectTypeIDs[$objectTypeID])) {
            return $this->getObjectTypeByName($this->objectTypeIDs[$objectTypeID]);
        }

        return null;
    }

    /**
     * Returns the category object type with the given name or `null` if no such object type exists.
     */
    public function getObjectTypeByName(string $objectType): ?ObjectType
    {
        return $this->objectTypes[$objectType] ?? null;
    }

    /**
     * Returns all category object types.
     *
     * @return  ObjectType[]
     */
    public function getObjectTypes(): array
    {
        return $this->objectTypes;
    }

    #[\Override]
    protected function init()
    {
        $this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.category');
        foreach ($this->objectTypes as $objectType) {
            $this->objectTypeIDs[$objectType->objectTypeID] = $objectType->objectType;
        }

        $this->cache = (new CategoryCache())->getCache();
    }

    /**
     * Reloads the category cache.
     */
    public function reloadCache(): void
    {
        $this->init();
    }
}
