<?php

namespace wcf\data\category;

use wcf\data\DatabaseObjectDecorator;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;

/**
 * Abstract implementation of a decorated category.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Category
 *
 * @method  Category    getDecoratedObject()
 * @mixin   Category
 */
abstract class AbstractDecoratedCategory extends DatabaseObjectDecorator
{
    /**
     * list of child categories of this category
     * @var Category[]
     */
    protected $childCategories;

    /**
     * list of all child categories of this category
     * @var Category[]
     */
    protected $allChildCategories;

    /**
     * list of all parent category generations of this category
     * @var AbstractDecoratedCategory[]
     */
    protected $parentCategories;

    /**
     * parent category of this category
     * @var AbstractDecoratedCategory
     */
    protected $parentCategory;

    /**
     * @inheritDoc
     */
    protected static $baseClass = Category::class;

    /**
     * @inheritDoc
     */
    public function checkPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->getPermission($permission)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getChildCategories()
    {
        if ($this->childCategories === null) {
            $this->childCategories = [];
            foreach ($this->getDecoratedObject()->getChildCategories() as $category) {
                $this->childCategories[$category->categoryID] = new static($category);
            }
        }

        return $this->childCategories;
    }

    /**
     * @inheritDoc
     */
    public function getAllChildCategories()
    {
        if ($this->allChildCategories === null) {
            $this->allChildCategories = [];
            foreach ($this->getDecoratedObject()->getAllChildCategories() as $category) {
                $this->allChildCategories[$category->categoryID] = new static($category);
            }
        }

        return $this->allChildCategories;
    }

    /**
     * @inheritDoc
     */
    public function getParentCategories()
    {
        if ($this->parentCategories === null) {
            $this->parentCategories = [];
            foreach ($this->getDecoratedObject()->getParentCategories() as $category) {
                $this->parentCategories[$category->categoryID] = new static($category);
            }
        }

        return $this->parentCategories;
    }

    /**
     * @inheritDoc
     */
    public function getParentCategory()
    {
        if ($this->parentCategoryID && $this->parentCategory === null) {
            $this->parentCategory = new static($this->getDecoratedObject()->getParentCategory());
        }

        return $this->parentCategory;
    }

    /**
     * @inheritDoc
     */
    public function isParentCategory(self $category)
    {
        return $this->getDecoratedObject()->isParentCategory($category->getDecoratedObject());
    }

    /**
     * Returns the decorated category with the given id or `null` if no such
     * category exists.
     *
     * @param int $categoryID
     * @return  AbstractDecoratedCategory|null
     */
    public static function getCategory($categoryID)
    {
        $category = CategoryHandler::getInstance()->getCategory($categoryID);
        if ($category) {
            return new static($category);
        }
    }
}
