<?php

namespace wcf\data\category;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\ILinkableObject;
use wcf\data\IObjectTreeNode;
use wcf\data\TObjectTreeNode;

/**
 * Represents a category node.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Category    getDecoratedObject()
 * @mixin   Category
 */
class CategoryNode extends DatabaseObjectDecorator implements IObjectTreeNode
{
    use TObjectTreeNode;

    /**
     * @inheritDoc
     */
    protected static $baseClass = Category::class;

    /**
     * Returns true if this category is visible in a nested menu item list.
     *
     * @since       5.2
     */
    public function isVisibleInNestedList(?AbstractDecoratedCategory $activeCategory = null, bool $showChildCategories = false): bool
    {
        if (!$this->getParentCategory()) {
            // level 1 is always visible
            return true;
        }

        if ($showChildCategories && !$this->getParentCategory()->getParentCategory()) {
            return true;
        }

        if ($activeCategory) {
            $decoratedObject = $this->getDecoratedObject();
            if (
                $activeCategory->categoryID == $this->categoryID
                || (
                    $decoratedObject instanceof AbstractDecoratedCategory
                    && $activeCategory->isParentCategory($decoratedObject)
                )
            ) {
                // is the active category or a parent of the active category
                return true;
            }

            if ($this->getParentCategory()->categoryID == $activeCategory->categoryID) {
                // is a direct child element of the active category
                return true;
            }

            foreach ($activeCategory->getParentCategories() as $parentCategory) {
                if ($this->getParentCategory()->categoryID == $parentCategory->categoryID) {
                    // This is a child element of a parent category of the active category.
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns number of items in the category.
     */
    public function getItems(): int
    {
        return 0;
    }

    public function getLink(): string
    {
        $decoratedObject = $this->getDecoratedObject();
        if ($decoratedObject instanceof ILinkableObject) {
            return $decoratedObject->getLink();
        }

        return '';
    }
}
