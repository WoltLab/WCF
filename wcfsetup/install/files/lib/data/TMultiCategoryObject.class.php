<?php

namespace wcf\data;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\system\exception\ParentClassException;
use wcf\system\WCF;

/**
 * Provides category-related methods for an object with multiple categories.
 *
 * Requires the following static methods:
 *  - public static function getCategoryMappingDatabaseTableName()
 *      returns the name of the database table containing the mapping of the objects to their categories
 *  - public static function getCategoryClassName()
 *      returns the name of the used AbstractDecoratedCategory class
 *  - public static function getDatabaseTableIndexName()
 *      see IStorableObject::getDatabaseTableIndexName()
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TMultiCategoryObject
{
    /**
     * list of the object's categories
     * @var AbstractDecoratedCategory[]
     */
    protected $categories;

    /**
     * ids of the object's categories
     * @var int[]
     */
    protected $categoryIDs = [];

    /**
     * list of the object's leaf categories
     * @var AbstractDecoratedCategory[]
     */
    protected $leafCategories;

    /**
     * Returns the list of category ids.
     *
     * @return  int[]
     */
    public function getCategoryIDs()
    {
        return $this->categoryIDs;
    }

    /**
     * Returns the categories of the object.
     *
     * @return  AbstractDecoratedCategory[]
     * @throws  ParentClassException
     */
    public function getCategories()
    {
        if ($this->categories === null) {
            $this->categories = [];

            $className = static::getCategoryClassName();
            if (!\is_subclass_of($className, AbstractDecoratedCategory::class)) {
                throw new ParentClassException($className, AbstractDecoratedCategory::class);
            }

            if (!empty($this->categoryIDs)) {
                foreach ($this->categoryIDs as $categoryID) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->categories[$categoryID] = $className::getCategory($categoryID);
                }
            } else {
                $sql = "SELECT      categoryID
                        FROM        wcf1_category
                        WHERE       categoryID IN (
                                        SELECT  categoryID
                                        FROM    " . static::getCategoryMappingDatabaseTableName() . "
                                        WHERE   " . static::getDatabaseTableIndexName() . " = ?
                                    )
                        ORDER BY    parentCategoryID, showOrder";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$this->getObjectID()]);
                while ($categoryID = $statement->fetchColumn()) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->categories[$categoryID] = $className::getCategory($categoryID);
                }
            }
        }

        return $this->categories;
    }

    /**
     * Returns the list of all selected categories unless a child category is selected.
     *
     * @return  AbstractDecoratedCategory[]
     */
    public function getLeafCategories()
    {
        if ($this->leafCategories === null) {
            $this->leafCategories = $categories = $this->getCategories();

            foreach ($categories as $category) {
                if ($category->parentCategoryID && isset($this->leafCategories[$category->parentCategoryID])) {
                    unset($this->leafCategories[$category->parentCategoryID]);
                }
            }
        }

        return $this->leafCategories;
    }

    /**
     * @see DatabaseObject::getObjectID()
     */
    abstract public function getObjectID();

    /**
     * Sets a category id.
     *
     * @param int $categoryID
     */
    public function setCategoryID($categoryID)
    {
        $this->categoryIDs[] = $categoryID;
    }

    /**
     * Sets a category ids.
     *
     * @param int[] $categoryIDs
     */
    public function setCategoryIDs(array $categoryIDs)
    {
        $this->categoryIDs = $categoryIDs;
    }
}
