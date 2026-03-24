<?php

namespace wcf\data\category;

use wcf\system\category\CategoryHandler;

/**
 * Represents an uncached tree of category nodes.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UncachedCategoryNodeTree extends CategoryNodeTree
{
    /**
     * locally cached categories
     * @var array<int, Category>
     */
    protected $categoryCache = [];

    /**
     * locally cached category ids grouped by the id of their parent category
     * @var array<int, list<int>>
     */
    protected $categoryStructureCache = [];

    #[\Override]
    protected function buildTree()
    {
        $categoryList = new CategoryList();
        $categoryList->getConditionBuilder()->add(
            'category.objectTypeID = ?',
            [CategoryHandler::getInstance()->getObjectTypeByName($this->objectType)->objectTypeID]
        );
        $categoryList->sqlOrderBy = "category.showOrder ASC";
        $categoryList->readObjects();
        foreach ($categoryList as $category) {
            if (!isset($this->categoryStructureCache[$category->parentCategoryID])) {
                $this->categoryStructureCache[$category->parentCategoryID] = [];
            }

            $this->categoryStructureCache[$category->parentCategoryID][] = $category->categoryID;
            $this->categoryCache[$category->categoryID] = $category;
        }

        parent::buildTree();
    }

    #[\Override]
    protected function getCategory(int $categoryID)
    {
        return $this->categoryCache[$categoryID];
    }

    #[\Override]
    protected function getChildCategories(CategoryNode $parentNode)
    {
        $categories = [];
        if (isset($this->categoryStructureCache[$parentNode->categoryID])) {
            foreach ($this->categoryStructureCache[$parentNode->categoryID] as $categoryID) {
                $categories[$categoryID] = $this->getCategory($categoryID);
            }
        }

        return $categories;
    }
}
