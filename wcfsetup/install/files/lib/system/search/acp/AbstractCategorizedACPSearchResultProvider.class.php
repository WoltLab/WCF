<?php

namespace wcf\system\search\acp;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\SystemException;

/**
 * Abstract implementation of a ACP search result provider with nested categories.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractCategorizedACPSearchResultProvider extends AbstractACPSearchResultProvider
{
    /**
     * list of categories
     * @var DatabaseObject[]
     */
    protected $categories = [];

    /**
     * class name for category list
     * @var string
     */
    protected $listClassName = '';

    /**
     * list of top category names (level 1 and 2)
     * @var string[]
     */
    protected $topCategories = [];

    public function __construct()
    {
        $this->loadCategories();
    }

    /**
     * Returns a level 1 or 2 category id for given category name.
     *
     * @return  int
     */
    protected function getCategoryID(string $categoryName)
    {
        // @phpstan-ignore property.notFound
        return $this->getTopCategory($categoryName)->categoryID;
    }

    /**
     * Returns a level 1 or 2 category name for given category name.
     *
     * @return  string
     */
    protected function getCategoryName(string $categoryName)
    {
        // @phpstan-ignore property.notFound
        return $this->getTopCategory($categoryName)->categoryName;
    }

    /**
     * Returns a level 1 or 2 category for given category name.
     *
     * @return  DatabaseObject
     * @throws  SystemException
     */
    protected function getTopCategory(string $categoryName)
    {
        if (!$this->isValid($categoryName)) {
            throw new SystemException("Category name '" . $categoryName . "' is unknown");
        }

        // this is a top category
        if (\in_array($categoryName, $this->topCategories)) {
            return $this->categories[$categoryName];
        }

        // check parent category
        // @phpstan-ignore property.notFound
        return $this->getTopCategory($this->categories[$categoryName]->parentCategoryName);
    }

    /**
     * Loads categories.
     *
     * @return void
     */
    protected function loadCategories()
    {
        // validate list class name
        if (empty($this->listClassName) || !\is_subclass_of($this->listClassName, DatabaseObjectList::class)) {
            throw new SystemException("Given class '" . $this->listClassName . "' is empty or invalid");
        }

        // read categories
        /** @var DatabaseObjectList<DatabaseObject> $categoryList */
        $categoryList = new $this->listClassName();
        $categoryList->readObjects();

        foreach ($categoryList as $category) {
            // validate options and permissions
            if (!$this->validate($category)) {
                continue;
            }

            // save level 1 categories
            // @phpstan-ignore property.notFound
            if ($category->parentCategoryName === '') {
                // @phpstan-ignore property.notFound
                $this->topCategories[] = $category->categoryName;
            }

            // @phpstan-ignore property.notFound
            $this->categories[$category->categoryName] = $category;
        }

        // create level 2 categories
        $topCategories = [];
        foreach ($this->categories as $key => $category) {
            // @phpstan-ignore property.notFound
            if ($category->parentCategoryName) {
                // check if parent category exists, thus if it is valid; if is does not exist, then all
                // child categories are also invalid
                if (!isset($this->categories[$category->parentCategoryName])) {
                    unset($this->categories[$key]);
                } elseif (\in_array($category->parentCategoryName, $this->topCategories)) {
                    // @phpstan-ignore property.notFound
                    $topCategories[] = $category->categoryName;
                }
            }
        }

        $this->topCategories = \array_merge($this->topCategories, $topCategories);
    }

    /**
     * Returns true if given category is valid and accessible.
     *
     * @return  bool
     */
    protected function isValid(string $categoryName)
    {
        return isset($this->categories[$categoryName]);
    }
}
